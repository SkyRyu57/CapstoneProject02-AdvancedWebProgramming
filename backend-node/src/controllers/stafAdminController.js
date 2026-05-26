const Inventory = require('../models/Inventory');
const ProcurementDraft = require('../models/ProcurementDraft');
const ProcurementDraftItem = require('../models/ProcurementDraftItem');
const ProcurementReceipt = require('../models/ProcurementReceipt');
const Room = require('../models/Room');

exports.approvedDrafts = async (req, res, next) => {
  try {
    const drafts = await ProcurementDraft.listFinalized();
    const items = await ProcurementDraftItem.listByDraftIds(drafts.map((draft) => draft._id));

    res.json({
      drafts: drafts.map((draft) => ({
        ...draft,
        items: items.filter((item) => item.draft_id === draft._id && item.approval_status === 'approved'),
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
    const { label_code, room_id, condition, status } = req.body;
    const qrCodePath = req.file ? `/uploads/qr-codes/${req.file.filename}` : req.body.existing_qr_code;

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
