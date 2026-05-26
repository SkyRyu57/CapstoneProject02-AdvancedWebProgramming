const ProcurementDraft = require('../models/ProcurementDraft');
const ProcurementDraftItem = require('../models/ProcurementDraftItem');

async function draftPayload(draftId = null) {
  const drafts = draftId
    ? [await ProcurementDraft.findById(draftId)].filter(Boolean)
    : await ProcurementDraft.listForReview();
  const items = await ProcurementDraftItem.listByDraftIds(drafts.map((draft) => draft._id));

  return drafts.map((draft) => ({
    ...draft,
    locked: draft.status === 'finalized',
    items: items.filter((item) => item.draft_id === draft._id),
  }));
}

exports.index = async (req, res, next) => {
  try {
    res.json({ drafts: await draftPayload() });
  } catch (error) {
    next(error);
  }
};

exports.show = async (req, res, next) => {
  try {
    const drafts = await draftPayload(Number(req.params.id));

    if (!drafts.length) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    return res.json({ draft: drafts[0] });
  } catch (error) {
    return next(error);
  }
};

exports.reviewItem = async (req, res, next) => {
  try {
    const approvalStatus = req.body.approval_status;
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    if (draft.status === 'finalized') {
      return res.status(422).json({ message: 'Draf sudah final dan tidak dapat diubah.' });
    }

    if (!['approved', 'rejected', 'pending'].includes(approvalStatus)) {
      return res.status(422).json({ message: 'Status review tidak valid.' });
    }

    const item = await ProcurementDraftItem.findById(req.params.itemId);

    if (!item || item.draft_id !== draft._id) {
      return res.status(404).json({ message: 'Item draf tidak ditemukan.' });
    }

    res.json({
      message: 'Status item berhasil diperbarui.',
      item: await ProcurementDraftItem.review(item._id, approvalStatus),
    });
  } catch (error) {
    next(error);
  }
};

exports.finalize = async (req, res, next) => {
  try {
    const draft = await ProcurementDraft.findById(req.params.id);

    if (!draft) {
      return res.status(404).json({ message: 'Draf tidak ditemukan.' });
    }

    res.json({
      message: 'Draf berhasil difinalisasi.',
      draft: await ProcurementDraft.finalize(draft._id, req.user._id),
    });
  } catch (error) {
    next(error);
  }
};
