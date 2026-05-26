const BaseModel = require('./BaseModel');

class Consumable extends BaseModel {
  static get collectionName() {
    return 'consumables';
  }

  static countLowStock() {
    return this.count({ $expr: { $lte: ['$stock', '$min_stock'] } });
  }

  static listLowStock(limit = 6) {
    return this.findMany(
      { $expr: { $lte: ['$stock', '$min_stock'] } },
      { sort: { stock: 1 }, limit },
    );
  }

  static listAll(limit = 200) {
    return this.findMany({}, { sort: { name: 1 }, limit });
  }

  static findById(id) {
    return this.findOne({ _id: Number(id) });
  }

  /**
   * Adjust stok BHP (positif = masuk, negatif = keluar).
   * Stok tidak boleh kurang dari 0.
   */
  static async adjustStock(id, quantityChange) {
    const numericId = Number(id);
    const consumable = await this.findOne({ _id: numericId });

    if (!consumable) return null;

    const newStock = Math.max(0, (consumable.stock || 0) + Number(quantityChange));

    return this.updateById(numericId, { stock: newStock });
  }
}

module.exports = Consumable;
