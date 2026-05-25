require('dotenv').config();

const cors = require('cors');
const express = require('express');
const authController = require('./controllers/authController');
const dashboardController = require('./controllers/dashboardController');
const authenticate = require('./middleware/authenticate');

const app = express();

app.use(cors({
  origin: process.env.CORS_ORIGIN || 'http://127.0.0.1:8000',
  credentials: true,
}));
app.use(express.json());

app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', service: 'lab-asset-api' });
});

app.post('/api/auth/login', authController.login);
app.post('/api/auth/forgot-account', authController.forgotAccount);
app.get('/api/auth/me', authenticate, authController.me);
app.get('/api/dashboard', authenticate, dashboardController.show);

app.use((req, res) => {
  res.status(404).json({ message: 'Endpoint tidak ditemukan.' });
});

app.use((error, req, res, next) => {
  console.error(error);
  res.status(error.status || 500).json({
    message: error.message || 'Terjadi kesalahan pada server.',
  });
});

module.exports = app;
