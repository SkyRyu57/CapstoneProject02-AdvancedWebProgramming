const mongoose = require('mongoose');
const bcrypt = require('bcrypt');

/**
 * User Schema
 * Merepresentasikan pengguna sistem manajemen aset laboratorium.
 * Role yang tersedia:
 *  - admin         : Administrator sistem
 *  - kepala_lab    : Kepala laboratorium (berwenang membuat draft pengadaan)
 *  - kaprodi       : Ketua program studi (mereview/menyetujui pengadaan)
 *  - staf_admin    : Staf administrasi (menerima barang pengadaan)
 *  - staf_lab      : Staf laboratorium (melakukan maintenance, piket)
 */
const userSchema = new mongoose.Schema(
  {
    name: {
      type: String,
      required: [true, 'Nama wajib diisi'],
      trim: true,
      maxlength: [100, 'Nama tidak boleh lebih dari 100 karakter'],
    },
    email: {
      type: String,
      required: [true, 'Email wajib diisi'],
      unique: true,
      lowercase: true,
      trim: true,
      match: [/^\S+@\S+\.\S+$/, 'Format email tidak valid'],
    },
    password: {
      type: String,
      required: [true, 'Password wajib diisi'],
      minlength: [6, 'Password minimal 6 karakter'],
      select: false, // Tidak dikembalikan secara default pada query
    },
    role: {
      type: String,
      required: [true, 'Role wajib diisi'],
      enum: {
        values: ['admin', 'kepala_lab', 'kaprodi', 'staf_admin', 'staf_lab'],
        message: 'Role tidak valid. Pilih: admin, kepala_lab, kaprodi, staf_admin, staf_lab',
      },
      default: 'staf_lab',
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' },
    versionKey: false,
  }
);

// --- Indexes ---
userSchema.index({ email: 1 }, { unique: true });
userSchema.index({ role: 1 });

// --- Middleware (Hooks) ---

/**
 * Hash password sebelum disimpan ke database.
 * Hanya dijalankan jika field password diubah.
 */
userSchema.pre('save', async function (next) {
  if (!this.isModified('password')) return next();
  try {
    const saltRounds = parseInt(process.env.BCRYPT_ROUNDS, 10) || 12;
    this.password = await bcrypt.hash(this.password, saltRounds);
    next();
  } catch (err) {
    next(err);
  }
});

// --- Instance Methods ---

/**
 * Membandingkan password yang diberikan dengan hash yang tersimpan.
 * @param {string} candidatePassword - Password plain text yang akan diverifikasi
 * @returns {Promise<boolean>}
 */
userSchema.methods.comparePassword = async function (candidatePassword) {
  return bcrypt.compare(candidatePassword, this.password);
};

/**
 * Mengembalikan representasi user tanpa field sensitif.
 * @returns {Object}
 */
userSchema.methods.toPublicJSON = function () {
  const obj = this.toObject();
  delete obj.password;
  return obj;
};

// --- Static Methods ---

/**
 * Mencari user berdasarkan email dan menyertakan field password (untuk autentikasi).
 * @param {string} email
 * @returns {Promise<User|null>}
 */
userSchema.statics.findByEmailWithPassword = function (email) {
  return this.findOne({ email: email.toLowerCase() }).select('+password');
};

const User = mongoose.model('User', userSchema);

module.exports = User;
