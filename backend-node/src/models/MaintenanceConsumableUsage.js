const BaseModel = require('./BaseModel');

/**
 * MaintenanceConsumableUsage Model
 * Merepresentasikan penggunaan consumable selama kegiatan maintenance.
 * Menghubungkan satu log maintenance dengan satu consumable beserta jumlah yang dipakai.
 */
class MaintenanceConsumableUsage extends BaseModel {
  static get collectionName() {
    return 'maintenance_consumable_usages';
  }

  /**
   * Mendapatkan semua penggunaan consumable untuk suatu log maintenance.
   * @param {ObjectId} maintenanceLogId
   */
  static listByMaintenanceLog(maintenanceLogId) {
    return this.findMany({ maintenance_log_id: maintenanceLogId });
  }

  /**
   * Mendapatkan semua penggunaan untuk suatu consumable.
   * @param {ObjectId} consumableId
   * @param {number} limit
   */
  static listByConsumable(consumableId, limit = 20) {
    return this.findMany(
      { consumable_id: consumableId },
      { sort: { _id: -1 }, limit },
    );
  }
}

module.exports = MaintenanceConsumableUsage;
