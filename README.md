
# Smart Pantry – Food Inventory & Expiry Tracker

A web-based food inventory management system that helps users track pantry items, monitor expiry dates, and manage grocery expenses.

## Overview

Smart Pantry is designed to reduce food waste and make grocery management easier. Users can maintain a record of food items, identify items approaching their expiry dates, and plan grocery purchases using shopping lists and budget tracking.

## Features

- **Food Inventory Management** – Add and manage food items in the pantry.
- **Expiry Tracking** – Monitor expired and near-expiry food items.
- **Dashboard** – View key inventory information and recent items.
- **Monthly Budget Tracking** – Set a monthly grocery budget and monitor recorded spending.
- **Shopping List** – Add grocery items and estimate their total cost.
- **Shopping Trip Management** – Create shopping trips and associate purchases with trips.
- **Statistics** – View inventory-related information.
- **User Authentication** – Register, log in, and manage access to the application.

## Technology Stack

| Technology | Purpose |
|---|---|
| HTML | Page structure |
| CSS | User interface styling |
| JavaScript | Client-side interactions |
| PHP | Backend processing |
| MySQL | Database management |
| XAMPP | Local development server |
| Visual Studio Code | Development environment |
| Git and GitHub | Version control and source code hosting |

## Project Structure

```text
smart-pantry/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── config/
├── add_item.php
├── budget.php
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── notifications.php
├── pantry.php
├── register.php
├── shopping_list.php
├── shopping_trips.php
├── statistics.php
├── settings.php
├── .gitignore
└── README.md
```

Additional files may be present in the repository.

## Local Installation

### Prerequisites

- Windows
- XAMPP with Apache and MySQL
- PHP and MySQL
- A web browser

### Setup Instructions

1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Place or clone the project inside the XAMPP `htdocs` directory.
4. Create a MySQL database named `smart_pantry` using phpMyAdmin.
5. Set up the database tables required by the application.
6. Configure the database connection locally in `config/database.php`.
7. Open the application in your browser at:

   `http://localhost/smart_pantry/`

8. Register an account or log in to use the application.

**Note:** The database schema and local configuration must be set up separately. Never publish database passwords or other secret credentials.

## Budget Calculation

The monthly budget feature compares the user's configured monthly budget with recorded pantry purchase costs for the selected month.

The shopping list separately estimates planned grocery expenses using quantity and estimated unit price. Planned shopping costs and recorded purchase spending represent different values.

## Project Objectives

- Reduce food waste by improving expiry awareness.
- Make food inventory easier to maintain.
- Help users plan grocery purchases.
- Track monthly grocery expenses.
- Estimate shopping list costs before purchasing.
- Improve pantry organization and expense awareness.

## Future Improvements

- Automated expiry reminders.
- Improved data visualizations.
- Enhanced mobile responsiveness.
- More detailed spending reports.
- Additional shopping and budget insights.

## Project Status

Developed as an academic project. Features and documentation may evolve as development continues.

## Author

**Kolkar Thushmith**

GitHub: [kolkarthushmith2023-art](https://github.com/kolkarthushmith2023-art)

## License

No license has been specified yet.
