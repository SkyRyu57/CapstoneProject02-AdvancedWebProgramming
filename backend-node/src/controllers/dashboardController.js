const Dashboard = require('../models/Dashboard');

exports.show = async (req, res, next) => {
  try {
    return res.json(await Dashboard.forUser(req.user));
  } catch (error) {
    return next(error);
  }
};
