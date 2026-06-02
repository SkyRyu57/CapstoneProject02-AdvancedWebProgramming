const Inventory = require('../models/Inventory');
const Consumable = require('../models/Consumable');
const ProcurementDraft = require('../models/ProcurementDraft');
const ProcurementDraftItem = require('../models/ProcurementDraftItem');
const ProcurementReceipt = require('../models/ProcurementReceipt');
const Room = require('../models/Room');

exports.approvedDrafts = async (req, res, next) => {
  try {
    const drafts = await ProcurementDraft.listFinalized();
    const allItems = await ProcurementDraftItem.listByDraftIds(drafts.map((draft) => draft._id));
    const approvedItems = allItems.filter((item) => item.approval_status === 'approved');

    // Calculate total received quantities per item
    const receivedMap = await ProcurementReceipt.sumByDraftItemIds(approvedItems.map((i) => i._id));

    // Only include items that haven't been fully received
    const pendingItems = approvedItems.map((item) => ({
      ...item,
      total_received: receivedMap[item._id] || 0,
      remaining: item.quantity - (receivedMap[item._id] || 0),
    }));

    res.json({
      drafts: drafts.map((draft) => ({
        ...draft,
        items: pendingItems.filter(
          (item) => item.draft_id === draft._id && item.remaining > 0,
        ),
        fulfilled_items: pendingItems.filter(
          (item) => item.draft_id === draft._id && item.remaining <= 0,
        ),
      })),
      receipts: await ProcurementReceipt.listRecent(100),
    });
  } catch (error) {
    next(error);
  }
};

exports.storeReceipt = async (req, res, next) => {
  try {
    const { draft_item_id, received_date, quantity, notes } = req.body;

    if (!draft_item_id || !received_date || !quantity) {
      return res.status(422).json({ message: 'Item, tanggal diterima, dan jumlah wajib diisi.' });
    }

    const item = await ProcurementDraftItem.findById(draft_item_id);

    if (!item || item.approval_status !== 'approved') {
      return res.status(422).json({ message: 'Item belum disetujui atau tidak ditemukan.' });
    }

    res.status(201).json({
      message: 'Penerimaan barang berhasil dicatat.',
      receipt: await ProcurementReceipt.createReceipt({
        draft_item_id,
        received_date,
        quantity,
        notes,
        staf_admin_id: req.user._id,
      }),
    });
  } catch (error) {
    next(error);
  }
};

exports.inventories = async (req, res, next) => {
  try {
    res.json({
      inventories: await Inventory.listManageable(),
      rooms: await Room.listRecent(),
    });
  } catch (error) {
    next(error);
  }
};

exports.updateInventory = async (req, res, next) => {
  try {
    const { label_code, room_id, condition, status, use_generated_barcode } = req.body;

    // Determine QR code: generated from ID, uploaded file, or keep existing
    let qrCodePath;
    if (use_generated_barcode === 'true') {
      qrCodePath = `INV-${req.params.id}`;
    } else if (req.file) {
      qrCodePath = `/uploads/qr-codes/${req.file.filename}`;
    } else {
      qrCodePath = req.body.existing_qr_code;
    }

    if (!label_code || !room_id) {
      return res.status(422).json({ message: 'Nomor label dan ruangan wajib diisi.' });
    }

    const inventory = await Inventory.updateAssetData(req.params.id, {
      label_code,
      qr_code: qrCodePath,
      room_id,
      condition,
      status,
    });

    if (!inventory) {
      return res.status(404).json({ message: 'Inventaris tidak ditemukan.' });
    }

    return res.json({
      message: 'Data inventaris berhasil diperbarui.',
      inventory,
    });
  } catch (error) {
    next(error);
  }
};

exports.destroyInventory = async (req, res, next) => {
  try {
    const inventory = await Inventory.deleteInventory(req.params.id);

    if (!inventory) {
      return res.status(404).json({ message: 'Inventaris tidak ditemukan.' });
    }

    return res.json({ message: 'Inventaris berhasil dihapus.' });
  } catch (error) {
    return next(error);
  }
};

// GET /api/inventory-list – semua role kecuali admin
exports.inventoryList = async (req, res, next) => {
  try {
    const [inventories, consumables, rooms] = await Promise.all([
      Inventory.listManageable(),
      Consumable.listAll(),
      Room.listRecent(),
    ]);

    const roomMap = rooms.reduce((acc, r) => { acc[r._id] = r.name; return acc; }, {});

    res.json({
      inventories: inventories.map((inv) => ({
        ...inv,
        type: 'inventory',
        room_name: roomMap[inv.room_id] || '-',
      })),
      consumables: consumables.map((c) => ({
        ...c,
        type: 'consumable',
        is_low: c.stock <= c.min_stock,
      })),
    });
  } catch (error) {
    next(error);
  }
};
