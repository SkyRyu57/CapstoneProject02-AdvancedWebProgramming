const BaseModel = require('./BaseModel');

class InventoryMaintenanceLog extends BaseModel {
  static get collectionName() {
    return 'inventory_maintenance_logs';
  }

  static listRecent(limit = 6) {
    return this.findMany({}, { sort: { maintenance_date: -1 }, limit });
  }

  static listAll(limit = 200) {
    return this.findMany({}, { sort: { maintenance_date: -1 }, limit });
  }

  static listByInventory(inventoryId, limit = 50) {
    return this.findMany(
      { inventory_item_id: Number(inventoryId) },
      { sort: { maintenance_date: -1 }, limit },
    );
  }

  static findById(id) {
    return this.findOne({ _id: Number(id) });
  }

  static createLog(data) {
    return this.create({
      inventory_item_id: Number(data.inventory_item_id),
      staf_lab_id: Number(data.staf_lab_id),
      maintenance_date: new Date(data.maintenance_date),
      description: data.description || '',
      condition_before: data.condition_before || 'baik',
      condition_after: data.condition_after || 'baik',
    });
  }
}

module.exports = InventoryMaintenanceLog;
