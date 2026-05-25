/**
 * Models Index
 *
 * File ini mengekspor semua model data (BaseModel) dalam satu titik akses.
 * Untuk business logic / agregasi, lihat src/services/.
 *
 * Contoh penggunaan:
 *   const { User, Inventory, Consumable } = require('../models');
 */

const User = require('./User');
const Room = require('./Room');
const UserRoomShift = require('./UserRoomShift');
const ProcurementDraft = require('./ProcurementDraft');
const ProcurementDraftItem = require('./ProcurementDraftItem');
const ProcurementReceipt = require('./ProcurementReceipt');
const Inventory = require('./Inventory');
const Consumable = require('./Consumable');
const ConsumableStockAdjustment = require('./ConsumableStockAdjustment');
const InventoryMaintenanceLog = require('./InventoryMaintenanceLog');
const MaintenanceConsumableUsage = require('./MaintenanceConsumableUsage');

module.exports = {
  User,
  Room,
  UserRoomShift,
  ProcurementDraft,
  ProcurementDraftItem,
  ProcurementReceipt,
  Inventory,
  Consumable,
  ConsumableStockAdjustment,
  InventoryMaintenanceLog,
  MaintenanceConsumableUsage,
};
