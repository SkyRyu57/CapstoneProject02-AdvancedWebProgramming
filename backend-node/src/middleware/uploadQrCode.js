const path = require('path');
const multer = require('multer');

const uploadDir = path.join(__dirname, '../../public/uploads/qr-codes');

const storage = multer.diskStorage({
  destination: uploadDir,
  filename: (req, file, callback) => {
    const extension = path.extname(file.originalname).toLowerCase();
    const safeName = `qr-${Date.now()}-${Math.round(Math.random() * 1e9)}${extension}`;

    callback(null, safeName);
  },
});

const imageOnly = (req, file, callback) => {
  if (!file.mimetype.startsWith('image/')) {
    return callback(new Error('File QR/Barcode harus berupa gambar.'));
  }

  return callback(null, true);
};

module.exports = multer({
  storage,
  fileFilter: imageOnly,
  limits: {
    fileSize: 2 * 1024 * 1024,
  },
});
