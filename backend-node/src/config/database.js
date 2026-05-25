const mongoose = require('mongoose');

async function connectDatabase() {
  const uri = process.env.MONGODB_URI || 'mongodb://127.0.0.1:27017/lab_asset';

  mongoose.set('strictQuery', false);
  await mongoose.connect(uri);

  console.log('MongoDB connected');
}

module.exports = connectDatabase;
