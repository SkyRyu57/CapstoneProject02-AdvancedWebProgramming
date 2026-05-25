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
}

module.exports = Consumable;
