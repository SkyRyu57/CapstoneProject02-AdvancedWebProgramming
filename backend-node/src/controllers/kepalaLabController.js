const Inventory = require('../models/Inventory');
const ProcurementDraft = require('../models/ProcurementDraft');
const ProcurementDraftItem = require('../models/ProcurementDraftItem');

async function buildDraftPayload(draftId = null, kepalaLabId = null) {
  const drafts = draftId
    ? [await ProcurementDraft.findById(draftId)].filter(Boolean)
    : await ProcurementDraft.listByKepalaLab(kepalaLabId, 100);

  const items = await ProcurementDraftItem.listByDraftIds(drafts.map((d) => d._id));

  return drafts.map((draft) => ({
    ...draft,
    locked: draft.status === 'finalized',
    items: items.filter((item) => item.draft_id === draft._id),
  }));
}

// GET /api/kepala-lab/procurement-drafts
exports.index = async (req, res, next) => {
  try {
    res.json({ drafts: await buildDraftPayload(null, req.user._id) });
  } catch (error) {
    next(error);
  }
};

// POST /api/kepala-lab/procurement-drafts
exports.store = async (req, res, next) => {
  try {
    const { fiscal_year, notes } = req.body;

    if (!fiscal_year) {
      return res.status(422).json({ message: 'Tahun anggaran wajib diisi.' });
    }

    const draft = await ProcurementDraft.createDraft({
      kepala_lab_id: req.user._id,
      fiscal_year,
      notes,
    });

    return res.status(201).json({ message: 'Draf pengadaan berhasil dibuat.', draft });
  } catch (error) {
    return next(error);
  }
};

// GET /api/kepala-lab/procurement-drafts/:id
exports.show = async (req, res, next) => {
  try {
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft || draft.kepala_lab_id !== req.user._id) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    const payloads = await buildDraftPayload(draft._id);
    return res.json({ draft: payloads[0] });
  } catch (error) {
    return next(error);
  }
};

// PATCH /api/kepala-lab/procurement-drafts/:id
exports.update = async (req, res, next) => {
  try {
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft || draft.kepala_lab_id !== req.user._id) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    if (draft.status === 'finalized') {
      return res.status(422).json({ message: 'Draf sudah final dan tidak dapat diubah.' });
    }

    const { fiscal_year, notes } = req.body;

    if (!fiscal_year) {
      return res.status(422).json({ message: 'Tahun anggaran wajib diisi.' });
    }

    const updated = await ProcurementDraft.updateDraft(draft._id, { fiscal_year, notes });
    return res.json({ message: 'Draf berhasil diperbarui.', draft: updated });
  } catch (error) {
    return next(error);
  }
};

// DELETE /api/kepala-lab/procurement-drafts/:id
exports.destroy = async (req, res, next) => {
  try {
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft || draft.kepala_lab_id !== req.user._id) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    if (draft.status === 'finalized') {
      return res.status(422).json({ message: 'Draf sudah final dan tidak dapat dihapus.' });
    }

    await ProcurementDraft.deleteDraft(draft._id);
    return res.json({ message: 'Draf berhasil dihapus.' });
  } catch (error) {
    return next(error);
  }
};

// POST /api/kepala-lab/procurement-drafts/:id/items
exports.storeItem = async (req, res, next) => {
  try {
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft || draft.kepala_lab_id !== req.user._id) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    if (draft.status === 'finalized') {
      return res.status(422).json({ message: 'Draf sudah final dan tidak dapat diubah.' });
    }

    const { item_type, name, price, quantity, purchase_link, replacement_inventory_id } = req.body;

    if (!name || !item_type) {
      return res.status(422).json({ message: 'Nama dan tipe barang wajib diisi.' });
    }

    if (!['inventory', 'consumable'].includes(item_type)) {
      return res.status(422).json({ message: 'Tipe barang tidak valid.' });
    }

    const item = await ProcurementDraftItem.createItem({
      draft_id: draft._id,
      item_type,
      name,
      price,
      quantity,
      purchase_link,
      replacement_inventory_id,
    });

    return res.status(201).json({ message: 'Item berhasil ditambahkan.', item });
  } catch (error) {
    return next(error);
  }
};

// PATCH /api/kepala-lab/procurement-drafts/:id/items/:itemId
exports.updateItem = async (req, res, next) => {
  try {
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft || draft.kepala_lab_id !== req.user._id) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    if (draft.status === 'finalized') {
      return res.status(422).json({ message: 'Draf sudah final dan tidak dapat diubah.' });
    }

    const item = await ProcurementDraftItem.findById(req.params.itemId);

    if (!item || item.draft_id !== draft._id) {
      return res.status(404).json({ message: 'Item tidak ditemukan.' });
    }

    const { item_type, name, price, quantity, purchase_link, replacement_inventory_id } = req.body;

    if (!name || !item_type) {
      return res.status(422).json({ message: 'Nama dan tipe barang wajib diisi.' });
    }

    const updated = await ProcurementDraftItem.updateItem(item._id, {
      item_type,
      name,
      price,
      quantity,
      purchase_link,
      replacement_inventory_id,
    });

    return res.json({ message: 'Item berhasil diperbarui.', item: updated });
  } catch (error) {
    return next(error);
  }
};

// DELETE /api/kepala-lab/procurement-drafts/:id/items/:itemId
exports.destroyItem = async (req, res, next) => {
  try {
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft || draft.kepala_lab_id !== req.user._id) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    if (draft.status === 'finalized') {
      return res.status(422).json({ message: 'Draf sudah final dan tidak dapat diubah.' });
    }

    const item = await ProcurementDraftItem.findById(req.params.itemId);

    if (!item || item.draft_id !== draft._id) {
      return res.status(404).json({ message: 'Item tidak ditemukan.' });
    }

    await ProcurementDraftItem.destroyItem(item._id);
    return res.json({ message: 'Item berhasil dihapus.' });
  } catch (error) {
    return next(error);
  }
};

// GET /api/kepala-lab/inventories  (untuk dropdown replacement)
exports.inventories = async (req, res, next) => {
  try {
    res.json({ inventories: await Inventory.listManageable() });
  } catch (error) {
    next(error);
  }
};
