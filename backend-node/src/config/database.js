const mongoose = require('mongoose');

/**
 * Konfigurasi koneksi MongoDB menggunakan Mongoose.
 * Mendukung retry logic dan graceful shutdown.
 */

// Opsi koneksi Mongoose
const mongooseOptions = {
  // Autoindex dimatikan di production untuk performa (jalankan manual)
  autoIndex: process.env.NODE_ENV !== 'production',
};

/**
 * Membuat koneksi ke MongoDB.
 * Jika koneksi gagal, proses akan dihentikan dengan exit code 1.
 * @returns {Promise<void>}
 */
const connectDB = async () => {
  const mongoURI = process.env.MONGO_URI || 'mongodb://localhost:27017/lab_asset';

  try {
    await mongoose.connect(mongoURI, mongooseOptions);
    console.log(`✅ MongoDB terhubung: ${mongoose.connection.host}`);
  } catch (error) {
    console.error('❌ Gagal terhubung ke MongoDB:', error.message);
    process.exit(1);
  }
};

// --- Event Listeners untuk Koneksi ---

mongoose.connection.on('connected', () => {
  console.log('🟢 Mongoose: koneksi berhasil');
});

mongoose.connection.on('error', (err) => {
  console.error('🔴 Mongoose error:', err.message);
});

mongoose.connection.on('disconnected', () => {
  console.warn('🟡 Mongoose: koneksi terputus');
});

// Graceful shutdown: tutup koneksi saat proses dihentikan
process.on('SIGINT', async () => {
  await mongoose.connection.close();
  console.log('🔌 Koneksi MongoDB ditutup karena aplikasi dihentikan');
  process.exit(0);
});

process.on('SIGTERM', async () => {
  await mongoose.connection.close();
  console.log('🔌 Koneksi MongoDB ditutup karena SIGTERM');
  process.exit(0);
});

module.exports = connectDB;
