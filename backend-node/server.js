const app = require('./src/app');
const connectDatabase = require('./src/config/database');

const port = process.env.PORT || 5000;

connectDatabase()
  .then(() => {
    app.listen(port, () => {
      console.log(`Lab asset API is running on port ${port}`);
    });
  })
  .catch((error) => {
    console.error('Failed to start API:', error.message);
    process.exit(1);
  });
