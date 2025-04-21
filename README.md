# Laravel-Vue Stocks Dashboard

Web application for tracking stocks, cryptocurrencies, and financial options, featuring real-time indicators such as RSI, MACD, and the Fear & Greed Index. Built with Laravel, Vue 3, Inertia.js, Tailwind CSS, Vite, Shadcn-Vue Components, and TanStack Vue Table. It supports partial page reloads using Inertia 2.0 and offers strategic options analysis (e.g., straddles).

## Features

- **Real-time Financial Information & Indicators:** Company Info, Ticker Prices, Company Finantial Data, Ticker RSI, Ticker MACD, Fear & Market Greed Index.
- **Partial Page Reloads:** Efficient updates with Inertia.js (no full page refresh).
- **Options Analysis:** Parse and analyze manually inputted options data.
- **Interactive Tables:** Powered by TanStack Vue Table and Shadcn-Vue Components.

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Frontend:** Vue 3, Inertia.js v2, Tailwind CSS v4, Vite, Shadcn-Vue, TanStack Vue Table
- **Database:** PostgreSQL
- **Infrastructure:** Docker, Nginx

## Installation

### Clone Repository

```bash
git clone https://github.com/sezalazar/laravel-vue-stocks-analysis.git
cd laravel-vue-stocks
```

### Backend Setup

Install backend dependencies:

```bash
composer install
```

Configure environment variables:

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your DB credentials and API keys:

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

POLYGON_API_TOKEN=your_polygon_token
FINANCIALMODELINGPREP_API_TOKEN=your_fmp_token
FEAR_AND_GREED_API_HOST=your_fgi_api_host
FEAR_AND_GREED_API_KEY=your_fgi_api_key
```

### Database Migration

```bash
php artisan migrate
```

### Frontend Setup

Install and build assets:

```bash
npm install
npm run dev
```

### Starting the App

You can start the backend in two ways:

- **Using Laravel's built-in server:**

```bash
php artisan serve
```

- **Using Docker (recommended):**

Start Docker containers:

```bash
docker-compose up -d
# or
docker-compose up -d --build
```

Check initialization logs:

```bash
docker-compose logs laravel-vue-stocks
```

Execute migrations and artisan commands inside the Docker container:

```bash
docker-compose exec laravel-vue-stocks bash
```

Open [http://localhost:8000](http://localhost:8000) to access the app.

## Project Structure

### Controllers

- **`DashboardController`:** Fetch stocks, cryptos, and market data.
- **`OptionsController`:** Handles options data submissions.
- **`StockController`:** Fetches and transforms stock data (prices, company info, technical indicators) from configurable sources for frontend rendering via Inertia.js.

### Composables

- **`useOptions.js`:** Utilities for grouping options data by expiry date.
- **`useStockTable.ts`:** Utilities for formatting and displaying stock data in interactive tables (price, RSI, MACD, percent changes).

## Partial Reload Flow (Options)

- User submits options data via POST to `/options/process`.
- Server processes data and redirects with results stored in session.
- Inertia.js updates frontend components seamlessly without full reload.

### Example Data Format for Options

COME
74
183,00
183,50
41
183,00
1,81 %
338.342
179,75
------

16/Abr
9
32,939
37,550
10
32,939
19,17 %
1
27,640
0,00
1,000
150,00
------

0,520
4
-

2,250
-----

1
25,501
26,000
8
26,001
11,24 %
25
23,373
62,86
0,912
160,00
10
0,723
0,824
10
0,998
81,45 %
3
0,550
57,06
-0,094
2
16,251
17,200
9
17,201
12,42 %
75
15,300
54,38
0,815
170,00
10
2,050
3,299
1
2,060
-49,69 %
1
4,095
50,22
-0,188
20
10,450
11,490
10
12,000
26,12 %
707
9,515
64,41
0,616
180,00
10
4,533
7,479
10
--

8,000
-----

100
6,000
6,500
8
6,300
9,20 %
267
5,769
57,39
0,436
190,00
10
9,642
12,990
9
12,990
-0,76 %
4
13,090
68,33
-0,541
450
3,300
3,400
50
3,310
10,00 %
354
3,009
57,57
0,272
200,00
300
14,282
19,400
2
17,000
-10,53 %
129
19,000
46,37
-0,759
50
1,500
1,950
50
1,970
15,20 %
530
1,710
61,85
0,172
210,00
20
25,418
28,722
10
27,440
-8,97 %
25
30,143
67,78
-0,785
20
1,010
1,105
20
1,105
9,30 %
278
1,011
64,42
0,103
220,00
370
30,020
------

32,501
------

533
0,300
0,740
360
0,800
28,00 %
156
0,625
70,52
0,073
230,00
------

45,000
-4,26 %
2
47,000
58,61
-0,926
100
0,250
0,350
612
0,460
-3,16 %
8
0,475
72,40
0,044
240,00
340
47,675
62,890
230
---

47,500
------

552
0,101
0,300
1
-

0,332
-----

250,00
1
60,000
------

54,500
------

10
0,150
0,230
81
0,150
-34,78 %
10
0,230
75,55
0,016
260,00
------

5,040
-----

130
0,100
0,175
331
0,175
-30,00 %
120
0,250
84,26
0,017
270,00
------

0,163
1
0,150
-3,23 %
310
0,155
89,17
0,014
280,00
------

150
0,050
0,150
501
---

0,130
-----

300,00
------

1,100
26
--

0,151
-----

310,00
------

19/Jun
22
44,000
49,999
2
-

41,550
------

150,00
------

2
34,000
39,999
5
-

34,818
------

160,00
------

5,000
1.000
-----

30
26,100
33,950
15
26,010
24,45 %
295
20,900
37,99
0,803
170,00
------

3
17,001
20,500
1
19,000
14,97 %
4
16,526
52,17
0,578
190,00
1
15,500
17,000
14
17,000
13,33 %
2
15,000
54,72
-0,421
30
15,500
19,000
27
17,000
21,42 %
30
14,001
58,43
0,505
200,00
320
11,000
------

44,000
------

3
12,001
17,000
33
--

13,500
------

210,00
------

28
10,001
11,500
106
11,500
14,47 %
65
10,046
60,73
0,375
220,00
------

18,000
------

3
7,500
8,000
3
7,000
-3,94 %
20
7,287
53,43
0,282
230,00
------

25
4,001
6,400
37
--

7,000
-----

240,00
------

20
2,000
7,750
12
--

6,001
-----

250,00
------

50
1,500
7,980
185
---

5,000
-----

260,00
------

62,000
------

14/Ago
2
50,000
55,000
78
55,000
0,18 %
47
54,900
84,80
0,759
160,00
------

2
34,000
------

50,000
------

170,00
------

36,300
825
---

3,400
-----

1
22,101
------

190,00
------

1
25,000
27,400
1
25,000
-2,22 %
7
25,567
56,19
0,575
200,00
------

...

## Important Laravel Commands

Fetch and update latest market data (stored in the database due to API limits):

```bash
php artisan balanceSheet:fetch
php artisan companyData:fetch
php artisan macd:fetch
php artisan rsi:fetch
php artisan stockprices:fetch
```


## Configuration

### Assets to track (`config/tickers.php`)

```php
'merv' => [/* Merval stocks */],
'spy' => [/* SPY stocks */],
'criptoList' => [/* Cryptos */],
```

### Data Source Configuration (`config/stockdata.php`)

Choose between database (default) or API source:

```php
'db_api_source' => 'DB',  // 'API' or 'DB'
```

Stock prices API:

```php
'api_source' => env('STOCK_API_SOURCE', 'stockanalysis'),  // 'stockdata' or 'stockanalysis'
```

### Options strategies (`config/options.php`)

Define amount available and expected move:

```php
return [
    'available' => 200000, // Amount to invest
    'difference' => 7, // Percentage expected asset movement
];
```

## Docker Configuration

- **`docker-compose.yml`:** Defines Laravel, PostgreSQL, and Nginx services.
- **`Dockerfile`:** Builds the PHP-FPM environment with necessary extensions.
- **`nginx.conf`:** Nginx server configuration.
- **`entrypoint.sh`:** Initialization script handling environment variables, dependencies, migrations, and storage link setup.

## Code Formatting

The project includes a `.prettierrc` configuration file in the project root for consistent code formatting:

```json
{
  "printWidth": 140,
  "singleQuote": true,
  "semi": false,
  "htmlWhitespaceSensitivity": "ignore"
}
```

## Commit Guidelines

Use Conventional Commits for clarity:

```
feat(scope): short description
fix(scope): short description
chore: maintenance tasks
release: vX.Y.Z
```

Only commits with `release: vX.Y.Z` indicate new releases.

## Contributing

- Create branches (`feature/*`, `fix/*`) from main.
- Submit concise, well-documented pull requests referencing relevant issues.
- Squash merge with clear commit messages upon approval.
## Roadmap

- Integration of historical volatility analysis.
- Advanced strategy calculations (e.g., implied volatility via Black-Scholes).
- Python microservice for Monte Carlo simulations and advanced financial modeling.
- Enhanced frontend visualizations with interactive charts (ApexCharts).
- Expanded test coverage (unit and integration tests).

## License

MIT © Your Name
