const mongoose = require('mongoose');

/**
 * MaintenanceConsumableUsage Schema
 * Merepresentasikan penggunaan consumable selama kegiatan maintenance.
 * Setiap record menghubungkan satu log maintenance dengan satu consumable
 * dan mencatat jumlah consumable yang digunakan.
 *
 * Ketika record ini dibuat, stok consumable secara otomatis dikurangi
 * (ditangani di service layer, bukan di model ini).
 */
const maintenanceConsumableUsageSchema = new mongoose.Schema(
  {
    maintenance_log_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'InventoryMaintenanceLog',
      required: [true, 'ID log maintenance wajib diisi'],
    },
    consumable_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Consumable',
      required: [true, 'ID consumable wajib diisi'],
    },
    quantity_used: {
      type: Number,
      required: [true, 'Jumlah consumable yang digunakan wajib diisi'],
      min: [1, 'Jumlah yang digunakan minimal 1'],
      validate: {
        validator: Number.isInteger,
        message: 'Jumlah yang digunakan harus bilangan bulat',
      },
    },
  },
  {
    // Tidak ada timestamps karena data ini immutable setelah dibuat
    versionKey: false,
  }
);

// --- Indexes ---
maintenanceConsumableUsageSchema.index({ maintenance_log_id: 1 });
maintenanceConsumableUsageSchema.index({ consumable_id: 1 });
// Constraint unik: satu consumable hanya bisa muncul sekali per maintenance log
maintenanceConsumableUsageSchema.index(
  { maintenance_log_id: 1, consumable_id: 1 },
  { unique: true }
);

// --- Static Methods ---

/**
 * Mendapatkan semua penggunaan consumable untuk suatu log maintenance.
 * @param {ObjectId} maintenanceLogId
 * @returns {Promise<MaintenanceConsumableUsage[]>}
 */
maintenanceConsumableUsageSchema.statics.findByMaintenanceLogId = function (maintenanceLogId) {
  return this.find({ maintenance_log_id: maintenanceLogId }).populate(
    'consumable_id',
    'name unit'
  );
};

/**
 * Mendapatkan total penggunaan suatu consumable dalam rentang waktu.
 * Join dengan maintenance_logs untuk filter berdasarkan tanggal maintenance.
 * @param {ObjectId} consumableId
 * @param {Date} startDate
 * @param {Date} endDate
 * @returns {Promise<number>}
 */
maintenanceConsumableUsageSchema.statics.getTotalUsageByConsumable = async function (
  consumableId,
  startDate,
  endDate
) {
  const pipeline = [
    {
      $match: { consumable_id: new mongoose.Types.ObjectId(consumableId) },
    },
    {
      $lookup: {
        from: 'inventory_maintenance_logs',
        localField: 'maintenance_log_id',
        foreignField: '_id',
        as: 'log',
      },
    },
    { $unwind: '$log' },
  ];

  if (startDate || endDate) {
    const dateMatch = {};
    if (startDate) dateMatch.$gte = startDate;
    if (endDate) dateMatch.$lte = endDate;
    pipeline.push({ $match: { 'log.maintenance_date': dateMatch } });
  }

  pipeline.push({
    $group: {
      _id: null,
      total_used: { $sum: '$quantity_used' },
    },
  });

  const result = await this.aggregate(pipeline);
  return result.length > 0 ? result[0].total_used : 0;
};

/**
 * Mendapatkan top N consumable yang paling banyak digunakan untuk maintenance.
 * @param {number} limit - Jumlah top consumable (default: 10)
 * @returns {Promise<Object[]>}
 */
maintenanceConsumableUsageSchema.statics.getMostUsedConsumables = function (limit = 10) {
  return this.aggregate([
    {
      $group: {
        _id: '$consumable_id',
        total_used: { $sum: '$quantity_used' },
        usage_count: { $sum: 1 },
      },
    },
    {
      $lookup: {
        from: 'consumables',
        localField: '_id',
        foreignField: '_id',
        as: 'consumable',
      },
    },
    { $unwind: '$consumable' },
    {
      $project: {
        consumable_name: '$consumable.name',
        unit: '$consumable.unit',
        total_used: 1,
        usage_count: 1,
      },
    },
    { $sort: { total_used: -1 } },
    { $limit: limit },
  ]);
};

const MaintenanceConsumableUsage = mongoose.model(
  'MaintenanceConsumableUsage',
  maintenanceConsumableUsageSchema,
  'maintenance_consumable_usages'
);

module.exports = MaintenanceConsumableUsage;
