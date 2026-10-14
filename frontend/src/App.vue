<script setup>
import { onMounted, ref } from 'vue'
import api from './lib/api'

const token = ref(localStorage.getItem('uts_token') || '')
const user = ref(null)
const mode = ref('login')
const name = ref('')
const email = ref('')
const password = ref('')
const title = ref('')
const description = ref('')
const dueDate = ref('')
const tasks = ref([])
const error = ref('')
const success = ref('')
const loading = ref(false)

function messageFrom(errorObject) {
  const data = errorObject?.response?.data
  return data?.message || Object.values(data?.errors || {})[0]?.[0] || 'Terjadi kesalahan. Coba kembali.'
}

async function authenticate() {
  error.value = ''; success.value = ''; loading.value = true
  try {
    const body = { email: email.value, password: password.value }
    if (mode.value === 'register') body.name = name.value
    const response = await api.post(mode.value === 'register' ? '/register' : '/login', body)
    token.value = response.data.token
    localStorage.setItem('uts_token', token.value)
    user.value = response.data.user
    password.value = ''
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
  finally { loading.value = false }
}

async function loadTasks() {
  if (!token.value) return
  try {
    const [profile, list] = await Promise.all([api.get('/user'), api.get('/tasks')])
    user.value = profile.data.user
    tasks.value = list.data.data
  } catch (e) {
    if (e.response?.status === 401) clearSession()
    else error.value = messageFrom(e)
  }
}

async function addTask() {
  error.value = ''; success.value = ''
  try {
    await api.post('/tasks', {
      title: title.value,
      description: description.value || null,
      due_date: dueDate.value || null,
    })
    title.value = ''; description.value = ''; dueDate.value = ''
    success.value = 'Tugas berhasil ditambah.'
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

async function toggleTask(task) {
  error.value = ''
  try {
    await api.patch(`/tasks/${task.id}`, { status: task.status === 'done' ? 'pending' : 'done' })
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

async function editTask(task) {
  const newTitle = window.prompt('Ubah judul tugas:', task.title)
  if (newTitle === null) return
  if (!newTitle.trim()) { error.value = 'Judul tidak boleh kosong.'; return }
  try {
    await api.patch(`/tasks/${task.id}`, { title: newTitle.trim() })
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

async function deleteTask(task) {
  if (!window.confirm(`Hapus tugas "${task.title}"?`)) return
  try {
    await api.delete(`/tasks/${task.id}`)
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

function clearSession() {
  localStorage.removeItem('uts_token')
  token.value = ''; user.value = null; tasks.value = []
}

async function logout() {
  try { await api.post('/logout') } catch (_) { /* tetap bersihkan sesi lokal */ }
  clearSession()
  success.value = 'Logout berhasil.'
}

onMounted(loadTasks)
</script>

<template>
  <main class="wrap">
    <header><h1>UTS — Sistem Manajemen Tugas</h1><p>Laravel REST API + Vue.js + Axios</p></header>
    <div v-if="error" class="alert error">{{ error }}</div>
    <div v-if="success" class="alert success">{{ success }}</div>

    <section v-if="!token" class="card">
      <h2>{{ mode === 'login' ? 'Masuk' : 'Registrasi' }}</h2>
      <form @submit.prevent="authenticate">
        <label v-if="mode === 'register'">Nama <input v-model="name" required placeholder="Nama lengkap" /></label>
        <label>Email <input v-model="email" type="email" required placeholder="contoh@email.com" /></label>
        <label>Password <input v-model="password" type="password" minlength="8" required /></label>
        <button :disabled="loading">{{ loading ? 'Memproses...' : mode === 'login' ? 'Login' : 'Daftar' }}</button>
      </form>
      <p><button class="link" @click="mode = mode === 'login' ? 'register' : 'login'; error = ''">
        {{ mode === 'login' ? 'Belum punya akun? Daftar' : 'Sudah punya akun? Login' }}
      </button></p>
    </section>

    <template v-else>
      <section class="card flex"><span>Halo, <strong>{{ user?.name || 'Pengguna' }}</strong></span><button class="small" @click="logout">Logout</button></section>
      <section class="card">
        <h2>Tambah tugas</h2>
        <form @submit.prevent="addTask">
          <label>Judul <input v-model="title" required maxlength="255" placeholder="Mis. selesaikan laporan UTS" /></label>
          <label>Deskripsi <textarea v-model="description" rows="3" /></label>
          <label>Tenggat <input v-model="dueDate" type="date" /></label>
          <button>Tambah tugas</button>
        </form>
      </section>
      <section class="card">
        <h2>Daftar tugas ({{ tasks.length }})</h2>
        <p v-if="tasks.length === 0">Belum ada tugas. Tambahkan tugas pertama kamu.</p>
        <article v-for="task in tasks" :key="task.id" class="task">
          <div><strong :class="{ crossed: task.status === 'done' }">{{ task.title }}</strong>
            <p>{{ task.description || 'Tanpa deskripsi' }}</p>
            <small>Status: {{ task.status }} <span v-if="task.due_date"> | Tenggat: {{ task.due_date.slice(0, 10) }}</span></small>
          </div>
          <div class="actions"><button class="small" @click="toggleTask(task)">{{ task.status === 'done' ? 'Batal selesai' : 'Selesai' }}</button><button class="small" @click="editTask(task)">Edit</button><button class="small danger" @click="deleteTask(task)">Hapus</button></div>
        </article>
      </section>
    </template>
  </main>
</template>

<style>
* {box-sizing:border-box} body{margin:0;background:#f2f5f9;color:#18283b;font-family:system-ui,Arial,sans-serif}
.wrap{max-width:830px;margin:32px auto;padding:0 18px} header{margin-bottom:20px} h1{margin:0 0 8px;font-size:28px}
header p{color:#64748b;margin:0} h2{font-size:20px;margin:0 0 18px}.card{background:white;border:1px solid #dde5ed;border-radius:12px;padding:22px;margin:16px 0;box-shadow:0 2px 10px #1620330a}
label{display:block;margin-bottom:12px;font-weight:600}input,textarea{display:block;width:100%;border:1px solid #bcc8d6;border-radius:7px;padding:11px;font:inherit;margin-top:6px}
button{border:0;background:#1767d2;color:white;border-radius:8px;padding:10px 16px;cursor:pointer;font-weight:600}button:disabled{opacity:.6;cursor:wait}
button.small{padding:8px 10px;font-size:12px}button.danger{background:#bd3535}button.link{color:#1767d2;background:none;padding:0}
.flex{display:flex;justify-content:space-between;align-items:center}.task{display:flex;justify-content:space-between;gap:16px;border-top:1px solid #e5eaf0;padding:16px 0}
.task p{margin:6px 0;color:#4e6073}.task small{color:#64748b}.actions{display:flex;align-items:center;gap:5px;flex-wrap:wrap}.crossed{text-decoration:line-through;color:#6c7b8c}
.alert{padding:12px 15px;border-radius:8px;margin:10px 0}.error{background:#fee4e2;color:#9c2622}.success{background:#dcfce7;color:#166534}
@media(max-width:630px){.task{display:block}.actions{margin-top:12px}}
</style>
