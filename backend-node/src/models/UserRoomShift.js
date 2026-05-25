const mongoose = require('mongoose');

/**
 * UserRoomShift Schema
 * Merepresentasikan jadwal piket/shift staf lab di suatu ruangan.
 * Setiap shift memiliki tanggal, jam mulai, dan jam selesai.
 * Field finalized_at diisi ketika shift sudah selesai/dikonfirmasi.
 */
const userRoomShiftSchema = new mongoose.Schema(
  {
    user_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: [true, 'ID pengguna wajib diisi'],
    },
    room_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Room',
      required: [true, 'ID ruangan wajib diisi'],
    },
    shift_date: {
      type: Date,
      required: [true, 'Tanggal shift wajib diisi'],
    },
    start_time: {
      type: String,
      required: [true, 'Jam mulai shift wajib diisi'],
      match: [/^\d{2}:\d{2}(:\d{2})?$/, 'Format jam tidak valid (HH:MM atau HH:MM:SS)'],
    },
    end_time: {
      type: String,
      required: [true, 'Jam selesai shift wajib diisi'],
      match: [/^\d{2}:\d{2}(:\d{2})?$/, 'Format jam tidak valid (HH:MM atau HH:MM:SS)'],
    },
    finalized_at: {
      type: Date,
      default: null,
    },
  },
  {
    timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' },
    versionKey: false,
  }
);

// --- Indexes ---
userRoomShiftSchema.index({ user_id: 1 });
userRoomShiftSchema.index({ room_id: 1 });
userRoomShiftSchema.index({ shift_date: 1 });
userRoomShiftSchema.index({ user_id: 1, room_id: 1, shift_date: 1 });

// --- Virtual Fields ---

/**
 * Mengembalikan true jika shift sudah difinalisasi.
 */
userRoomShiftSchema.virtual('is_finalized').get(function () {
  return this.finalized_at !== null;
});

// --- Instance Methods ---

/**
 * Memfinalisasi shift (menandai sebagai selesai).
 * @returns {Promise<UserRoomShift>}
 */
userRoomShiftSchema.methods.finalize = function () {
  this.finalized_at = new Date();
  return this.save();
};

const UserRoomShift = mongoose.model('UserRoomShift', userRoomShiftSchema, 'user_room_shifts');

module.exports = UserRoomShift;
