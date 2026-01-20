# SALIKSIC EBM Calculator

**Systems and Learning for Inclusive Knowledge and Services in Innovation and Care**

A Laravel application to compute RR, ARR, NNT from study data, with DOI autofetch and individualized ARR calculations.

## Quick Start

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Configure Environment
Update `.env` file with your database credentials (already configured for MySQL):
- Database: `ebm`
- Host: `127.0.0.1`
- Username: `root`
- Password: Update as needed

### 3. Start Application
```bash
# Start Laravel development server
php artisan serve

# In another terminal, build assets
npm run dev
```

Visit: http://localhost:8000

## Features
- Study data entry with DOI autofetch
- RR/ARR/NNT calculations
- Individualized ARR calculations  
- Study management
- Reading journal
- Search by exposure/outcome

## Documentation
See [SETUP.md](SETUP.md) for detailed setup and migration information.

## License
Licensed under Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0).

© 2024 SALIKSIC Project. Some Rights Reserved.
