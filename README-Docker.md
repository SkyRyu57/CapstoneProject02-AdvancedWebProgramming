# Docker Setup: Laravel Blade + Node.js + MongoDB

Struktur folder:

```txt
lab-asset-system/
├── docker-compose.yml
├── frontend-laravel/
│   ├── Dockerfile
│   ├── .dockerignore
│   └── .env
└── backend-node/
    ├── Dockerfile
    ├── .dockerignore
    └── .env
```

## 1. Simpan file

Taruh `docker-compose.yml` di folder utama project:

```bash
lab-asset-system/docker-compose.yml
```

Taruh Dockerfile frontend di:

```bash
lab-asset-system/frontend-laravel/Dockerfile
```

Taruh Dockerfile backend di:

```bash
lab-asset-system/backend-node/Dockerfile
```

## 2. Atur .env backend-node

Buat atau ubah file:

```bash
backend-node/.env
```

Isi:

```env
PORT=5000
MONGO_URI=mongodb://mongodb:27017/lab_asset
JWT_SECRET=secret_key_lab_asset
```

Penting: karena Node.js jalan di Docker, host MongoDB bukan `localhost`, tapi `mongodb`.

## 3. Atur .env Laravel

Di file:

```bash
frontend-laravel/.env
```

Tambahkan:

```env
NODE_API_URL=http://backend-node:5000/api
```

Kalau Laravel diakses dari browser dan butuh request langsung dari JavaScript browser ke backend, pakai:

```env
NODE_API_URL_BROWSER=http://localhost:5000/api
```

## 4. Jalankan Docker

Dari folder utama `lab-asset-system`:

```bash
docker compose up -d --build
```

## 5. Cek container

```bash
docker ps
```

Harus muncul:

```txt
lab_asset_mongodb
lab_asset_backend
lab_asset_frontend
```

## 6. Akses aplikasi

Laravel:

```txt
http://localhost:8000
```

Node.js API:

```txt
http://localhost:5000
```

MongoDB:

```txt
mongodb://localhost:27017
```

## 7. Masuk ke MongoDB

```bash
docker exec -it lab_asset_mongodb mongosh
```

Lalu:

```js
use lab_asset
show collections
```

## 8. Jalankan query MongoDB dari file

Misalnya kamu punya file:

```bash
lab_asset_mongodb.js
```

Copy ke container:

```bash
docker cp lab_asset_mongodb.js lab_asset_mongodb:/lab_asset_mongodb.js
```

Jalankan:

```bash
docker exec -it lab_asset_mongodb mongosh /lab_asset_mongodb.js
```

## 9. Command penting

Matikan container:

```bash
docker compose down
```

Matikan dan hapus volume database:

```bash
docker compose down -v
```

Lihat log backend:

```bash
docker logs -f lab_asset_backend
```

Lihat log frontend:

```bash
docker logs -f lab_asset_frontend
```

Lihat log MongoDB:

```bash
docker logs -f lab_asset_mongodb
```
