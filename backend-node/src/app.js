require('dotenv').config();

const cors = require('cors');
const express = require('express');
const path = require('path');
const adminController = require('./controllers/adminController');
const authController = require('./controllers/authController');
const dashboardController = require('./controllers/dashboardController');
const kaprodiController = require('./controllers/kaprodiController');
const authenticate = require('./middleware/authenticate');
const authorize = require('./middleware/authorize');
const uploadQrCode = require('./middleware/uploadQrCode');
const stafAdminController = require('./controllers/stafAdminController');

const app = express();

app.use(cors({
  origin: process.env.CORS_ORIGIN || 'http://127.0.0.1:8000',
  credentials: true,
}));
app.use(express.json());
app.use('/uploads', express.static(path.join(__dirname, '../public/uploads')));

app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', service: 'lab-asset-api' });
});

app.post('/api/auth/login', authController.login);
app.post('/api/auth/forgot-account', authController.forgotAccount);
app.get('/api/auth/me', authenticate, authController.me);
app.get('/api/dashboard', authenticate, dashboardController.show);

app.get('/api/admin/users', authenticate, authorize('admin'), adminController.users);
app.post('/api/admin/users', authenticate, authorize('admin'), adminController.storeUser);
app.patch('/api/admin/users/:id', authenticate, authorize('admin'), adminController.updateUser);
app.delete('/api/admin/users/:id', authenticate, authorize('admin'), adminController.destroyUser);
app.get('/api/admin/rooms', authenticate, authorize('admin'), adminController.rooms);
app.post('/api/admin/rooms', authenticate, authorize('admin'), adminController.storeRoom);
app.patch('/api/admin/rooms/:id', authenticate, authorize('admin'), adminController.updateRoom);
app.delete('/api/admin/rooms/:id', authenticate, authorize('admin'), adminController.destroyRoom);

app.get('/api/kaprodi/procurement-drafts', authenticate, authorize('kaprodi'), kaprodiController.index);
app.get('/api/kaprodi/procurement-drafts/:id', authenticate, authorize('kaprodi'), kaprodiController.show);
app.patch('/api/kaprodi/procurement-drafts/:id/items/:itemId/review', authenticate, authorize('kaprodi'), kaprodiController.reviewItem);
app.patch('/api/kaprodi/procurement-drafts/:id/finalize', authenticate, authorize('kaprodi'), kaprodiController.finalize);

app.get('/api/staf-admin/approved-drafts', authenticate, authorize('staf_admin'), stafAdminController.approvedDrafts);
app.post('/api/staf-admin/receipts', authenticate, authorize('staf_admin'), stafAdminController.storeReceipt);
app.get('/api/staf-admin/inventories', authenticate, authorize('staf_admin'), stafAdminController.inventories);
app.patch('/api/staf-admin/inventories/:id', authenticate, authorize('staf_admin'), uploadQrCode.single('qr_code'), stafAdminController.updateInventory);
app.post('/api/staf-admin/inventories/:id', authenticate, authorize('staf_admin'), uploadQrCode.single('qr_code'), stafAdminController.updateInventory);
app.delete('/api/staf-admin/inventories/:id', authenticate, authorize('staf_admin'), stafAdminController.destroyInventory);

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
