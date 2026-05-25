const BaseModel = require('./BaseModel');

/**
 * UserRoomShift Model
 * Merepresentasikan jadwal piket/shift staf lab di suatu ruangan.
 * Field finalized_at diisi ketika shift sudah selesai/dikonfirmasi.
 */
class UserRoomShift extends BaseModel {
  static get collectionName() {
    return 'user_room_shifts';
  }

  /**
   * Mendapatkan semua shift berdasarkan user.
   * @param {ObjectId} userId
   */
  static listByUser(userId) {
    return this.findMany({ user_id: userId }, { sort: { shift_date: -1 } });
  }

  /**
   * Mendapatkan semua shift berdasarkan ruangan.
   * @param {ObjectId} roomId
   */
  static listByRoom(roomId) {
    return this.findMany({ room_id: roomId }, { sort: { shift_date: -1 } });
  }

  /**
   * Mendapatkan shift yang belum difinalisasi.
   */
  static listPending() {
    return this.findMany({ finalized_at: null }, { sort: { shift_date: 1 } });
  }
}

module.exports = UserRoomShift;
