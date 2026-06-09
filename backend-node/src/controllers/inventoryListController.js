const Inventory = require('../models/Inventory');

exports.index = async (req, res, next) => {
  try {
    const items = await Inventory.listManageable();
    res.json({ inventories: items });
  } catch (err) {
    next(err);
  }
};
