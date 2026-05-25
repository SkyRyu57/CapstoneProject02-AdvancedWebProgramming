const mongoose = require('mongoose');

/**
 * Room Schema
 * Merepresentasikan ruangan/laboratorium tempat aset berada.
 * Contoh: LAB-301 Laboratorium Jaringan, LAB-302 Laboratorium Pemrograman
 */
const roomSchema = new mongoose.Schema(
  {
    name: {
      type: String,
      required: [true, 'Nama ruangan wajib diisi'],
      unique: true,
      trim: true,
      maxlength: [100, 'Nama ruangan tidak boleh lebih dari 100 karakter'],
    },
    description: {
      type: String,
      trim: true,
      maxlength: [500, 'Deskripsi tidak boleh lebih dari 500 karakter'],
      default: null,
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' },
    versionKey: false,
  }
);

// --- Indexes ---
roomSchema.index({ name: 1 }, { unique: true });

// --- Virtual Relationships ---

/**
 * Mendapatkan semua inventaris yang berada di ruangan ini.
 */
roomSchema.virtual('inventories', {
  ref: 'Inventory',
  localField: '_id',
  foreignField: 'room_id',
});

/**
 * Mendapatkan semua consumable yang berada di ruangan ini.
 */
roomSchema.virtual('consumables', {
  ref: 'Consumable',
  localField: '_id',
  foreignField: 'room_id',
});

/**
 * Mendapatkan semua shift yang dijadwalkan di ruangan ini.
 */
roomSchema.virtual('shifts', {
  ref: 'UserRoomShift',
  localField: '_id',
  foreignField: 'room_id',
});

const Room = mongoose.model('Room', roomSchema);

module.exports = Room;
