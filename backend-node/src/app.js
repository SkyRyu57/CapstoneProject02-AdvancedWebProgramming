require('dotenv').config();

const cors = require('cors');
const express = require('express');
const path = require('path');
const adminController = require('./controllers/adminController');
const authController = require('./controllers/authController');
const dashboardController = require('./controllers/dashboardController');
const kaprodiController = require('./controllers/kaprodiController');
const kepalaLabController = require('./controllers/kepalaLabController');
const stafLabController = require('./controllers/stafLabController');
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

app.get('/api/kepala-lab/procurement-drafts', authenticate, authorize('kepala_lab'), kepalaLabController.index);
app.post('/api/kepala-lab/procurement-drafts', authenticate, authorize('kepala_lab'), kepalaLabController.store);
app.get('/api/kepala-lab/procurement-drafts/:id', authenticate, authorize('kepala_lab'), kepalaLabController.show);
app.patch('/api/kepala-lab/procurement-drafts/:id/submit', authenticate, authorize('kepala_lab'), kepalaLabController.submit);
app.patch('/api/kepala-lab/procurement-drafts/:id', authenticate, authorize('kepala_lab'), kepalaLabController.update);
app.delete('/api/kepala-lab/procurement-drafts/:id', authenticate, authorize('kepala_lab'), kepalaLabController.destroy);
app.post('/api/kepala-lab/procurement-drafts/:id/items', authenticate, authorize('kepala_lab'), kepalaLabController.storeItem);
app.patch('/api/kepala-lab/procurement-drafts/:id/items/:itemId', authenticate, authorize('kepala_lab'), kepalaLabController.updateItem);
app.delete('/api/kepala-lab/procurement-drafts/:id/items/:itemId', authenticate, authorize('kepala_lab'), kepalaLabController.destroyItem);
app.get('/api/kepala-lab/inventories', authenticate, authorize('kepala_lab'), kepalaLabController.inventories);

app.get('/api/staf-lab/consumables', authenticate, authorize('staf_lab'), stafLabController.consumables);
app.post('/api/staf-lab/consumables/:id/adjust', authenticate, authorize('staf_lab'), stafLabController.adjustStock);
app.get('/api/staf-lab/inventories', authenticate, authorize('staf_lab'), stafLabController.inventories);
app.get('/api/staf-lab/maintenance', authenticate, authorize('staf_lab'), stafLabController.maintenanceLogs);
app.get('/api/staf-lab/maintenance/:id', authenticate, authorize('staf_lab'), stafLabController.maintenanceLogDetail);
app.post('/api/staf-lab/maintenance', authenticate, authorize('staf_lab'), stafLabController.storeMaintenance);

// Shared inventory list – accessible by all roles except admin
app.get(
  '/api/inventory-list',
  authenticate,
  authorize('kepala_lab', 'kaprodi', 'staf_admin', 'staf_lab'),
  stafAdminController.inventoryList,
);

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
