const Consumable = require('../models/Consumable');
const ConsumableStockAdjustment = require('../models/ConsumableStockAdjustment');
const Inventory = require('../models/Inventory');
const InventoryMaintenanceLog = require('../models/InventoryMaintenanceLog');
const MaintenanceConsumableUsage = require('../models/MaintenanceConsumableUsage');

// GET /api/staf-lab/consumables
exports.consumables = async (req, res, next) => {
  try {
    res.json({ consumables: await Consumable.listAll() });
  } catch (error) {
    next(error);
  }
};

// POST /api/staf-lab/consumables/:id/adjust
exports.adjustStock = async (req, res, next) => {
  try {
    const { quantity_change, reason, reference_type } = req.body;
    const id = req.params.id;
    const numericChange = Number(quantity_change);

    if (!quantity_change || numericChange === 0 || Number.isNaN(numericChange)) {
      return res.status(422).json({ message: 'Jumlah perubahan stok wajib diisi dan tidak boleh 0.' });
    }

    const consumable = await Consumable.findById(id);

    if (!consumable) {
      return res.status(404).json({ message: 'BHP tidak ditemukan.' });
    }

    // Cegah stok negatif
    if (consumable.stock + numericChange < 0) {
      return res.status(422).json({
        message: `Stok tidak mencukupi. Stok saat ini: ${consumable.stock}.`,
      });
    }

    const [updated, adjustment] = await Promise.all([
      Consumable.adjustStock(id, numericChange),
      ConsumableStockAdjustment.create({
        consumable_id: Number(id),
        quantity_change: numericChange,
        reason: reason || '',
        reference_type: reference_type || 'manual',
        reference_id: null,
        created_by: req.user._id,
      }),
    ]);

    return res.json({
      message: 'Stok BHP berhasil diperbarui.',
      consumable: updated,
      adjustment,
    });
  } catch (error) {
    return next(error);
  }
};

// GET /api/staf-lab/inventories
exports.inventories = async (req, res, next) => {
  try {
    res.json({ inventories: await Inventory.listForStafLab() });
  } catch (error) {
    next(error);
  }
};

// GET /api/staf-lab/maintenance
exports.maintenanceLogs = async (req, res, next) => {
  try {
    const logs = await InventoryMaintenanceLog.listAll();
    const inventories = await Inventory.listForStafLab();
    const consumables = await Consumable.listAll();

    res.json({ logs, inventories, consumables });
  } catch (error) {
    next(error);
  }
};

// GET /api/staf-lab/maintenance/:id
exports.maintenanceLogDetail = async (req, res, next) => {
  try {
    const log = await InventoryMaintenanceLog.findById(Number(req.params.id));

    if (!log) {
      return res.status(404).json({ message: 'Log maintenance tidak ditemukan.' });
    }

    const [inventories, consumables, usages] = await Promise.all([
      Inventory.listForStafLab(),
      Consumable.listAll(),
      MaintenanceConsumableUsage.listByMaintenanceLog(log._id),
    ]);

    // Enrich usages with consumable names
    const enrichedUsages = usages.map((u) => ({
      ...u,
      consumable_name: consumables.find((c) => c._id === u.consumable_id)?.name || `BHP #${u.consumable_id}`,
      consumable_unit: consumables.find((c) => c._id === u.consumable_id)?.unit || '',
    }));

    const inventory = inventories.find((i) => i._id === log.inventory_item_id);

    return res.json({
      log: {
        ...log,
        inventory_name: inventory?.name || `Inventaris #${log.inventory_item_id}`,
        inventory_label: inventory?.label_code || '',
      },
      usages: enrichedUsages,
    });
  } catch (error) {
    return next(error);
  }
};

// POST /api/staf-lab/maintenance
exports.storeMaintenance = async (req, res, next) => {
  try {
    const {
      inventory_item_id,
      maintenance_date,
      description,
      condition_before,
      condition_after,
      status_after,
      consumable_usages, // array of { consumable_id, quantity_used }
    } = req.body;

    if (!inventory_item_id || !maintenance_date || !condition_before || !condition_after) {
      return res.status(422).json({
        message: 'Inventaris, tanggal, kondisi sebelum, dan kondisi sesudah wajib diisi.',
      });
    }

    const inventory = await Inventory.findById(inventory_item_id);

    if (!inventory) {
      return res.status(404).json({ message: 'Inventaris tidak ditemukan.' });
    }

    // Validasi stok BHP yang akan dipakai sebelum proses
    const usages = Array.isArray(consumable_usages) ? consumable_usages : [];

    for (const usage of usages) {
      if (!usage.consumable_id || !usage.quantity_used || usage.quantity_used <= 0) continue;

      const consumable = await Consumable.findById(usage.consumable_id);

      if (!consumable) {
        return res.status(422).json({ message: `BHP ID ${usage.consumable_id} tidak ditemukan.` });
      }

      if (consumable.stock < Number(usage.quantity_used)) {
        return res.status(422).json({
          message: `Stok ${consumable.name} tidak mencukupi (stok: ${consumable.stock}, dipakai: ${usage.quantity_used}).`,
        });
      }
    }

    // Buat log maintenance
    const log = await InventoryMaintenanceLog.createLog({
      inventory_item_id,
      staf_lab_id: req.user._id,
      maintenance_date,
      description,
      condition_before,
      condition_after,
    });

    // Update kondisi & status inventaris
    await Inventory.updateCondition(
      inventory_item_id,
      condition_after,
      status_after || null,
    );

    // Proses pemakaian BHP
    const usageRecords = [];

    for (const usage of usages) {
      if (!usage.consumable_id || !usage.quantity_used || Number(usage.quantity_used) <= 0) continue;

      const qtyUsed = Number(usage.quantity_used);

      const [usageRecord] = await Promise.all([
        MaintenanceConsumableUsage.create({
          maintenance_log_id: log._id,
          consumable_id: Number(usage.consumable_id),
          quantity_used: qtyUsed,
        }),
        Consumable.adjustStock(usage.consumable_id, -qtyUsed),
        ConsumableStockAdjustment.create({
          consumable_id: Number(usage.consumable_id),
          quantity_change: -qtyUsed,
          reason: `Pemakaian maintenance inventaris ID ${inventory_item_id}`,
          reference_type: 'maintenance',
          reference_id: log._id,
          created_by: req.user._id,
        }),
      ]);

      usageRecords.push(usageRecord);
    }

    return res.status(201).json({
      message: 'Log maintenance berhasil dicatat.',
      log,
      consumable_usages: usageRecords,
    });
  } catch (error) {
    return next(error);
  }
};
