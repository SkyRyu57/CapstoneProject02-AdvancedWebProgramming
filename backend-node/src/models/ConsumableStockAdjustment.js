const BaseModel = require('./BaseModel');

/**
 * ConsumableStockAdjustment Model
 * Merepresentasikan catatan perubahan stok consumable (audit trail).
 * quantity_change positif = barang masuk, negatif = barang keluar.
 *
 * reference_type: 'procurement_receipt' | 'maintenance' | 'manual'
 */
class ConsumableStockAdjustment extends BaseModel {
  static get collectionName() {
    return 'consumable_stock_adjustments';
  }

  /**
   * Mendapatkan riwayat perubahan stok untuk suatu consumable.
   * @param {ObjectId} consumableId
   * @param {number} limit
   */
  static listByConsumable(consumableId, limit = 50) {
    return this.findMany(
      { consumable_id: consumableId },
      { sort: { created_at: -1 }, limit },
    );
  }

  /**
   * Mendapatkan riwayat adjustment berdasarkan tipe referensi.
   * @param {string} referenceType - 'procurement_receipt' | 'maintenance' | 'manual'
   * @param {number} limit
   */
  static listByReferenceType(referenceType, limit = 20) {
    return this.findMany(
      { reference_type: referenceType },
      { sort: { created_at: -1 }, limit },
    );
  }
}

module.exports = ConsumableStockAdjustment;
