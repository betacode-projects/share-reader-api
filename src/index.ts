import express from 'express'
import { router } from './routes/router'

const app: express.Express = express()

app.use('/public', express.static(__dirname + '/public'))
app.use((req, res, next) => {
  res.header("Access-Control-Allow-Origin", "*")
  res.header("Access-Control-Allow-Headers", "Origin, X-Requested-Width, Conntent-Type, Accept")
  next()
})
app.use(express.json())
app.use(express.urlencoded({ extended: true }))
app.use(router)
app.use((req, res, next) => {
  res.status(404).send('Sorry can not find that!')
})

app.listen(3000, () => {
  console.log('nya app listening on port 3000!')
})