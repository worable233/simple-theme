import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import '@knadh/oat/oat.min.css'
import '@knadh/oat/oat.min.js'
import './styles/app.css'

const app = createApp(App)

app.use(router)
app.mount('#app')
