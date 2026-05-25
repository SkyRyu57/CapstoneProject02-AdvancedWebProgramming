const DashboardService = require('../services/DashboardService');

exports.show = async (req, res, next) => {
  try {
    return res.json(await DashboardService.forUser(req.user));
  } catch (error) {
    return next(error);
  }
};
