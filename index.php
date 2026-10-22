<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Smart Pantry</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;

            background: #e8eee1;

            font-family: Arial, Helvetica, sans-serif;
        }

        /* MOBILE APP FRAME */

        .app {
            width: 375px;
            height: 760px;

            position: relative;
            overflow: hidden;

            background: #f8f9ed;

            border-radius: 32px;

            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        }

        /* SUBTLE BACKGROUND PATTERN */

        .pattern {
            position: absolute;
            inset: -30px;

            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-auto-rows: 85px;

            transform: rotate(-8deg);

            opacity: 0.055;

            pointer-events: none;
        }

        .pattern span {
            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 27px;
        }

        /* MAIN CONTENT */

        .content {
            position: relative;
            z-index: 2;

            height: 100%;

            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ILLUSTRATION AREA */

        .illustration {
            width: 230px;
            height: 245px;

            margin-top: 58px;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .illustration svg {
            width: 220px;
            height: 220px;

            display: block;
        }

        /* BRAND */

        .brand {
            margin-top: 5px;

            color: #248733;

            font-size: 30px;

            font-weight: 700;

            letter-spacing: -0.8px;

            text-align: center;
        }

        /* SUBTITLE */

        .subtitle {
            margin-top: 8px;

            color: #3f3f3f;

            font-size: 13px;

            font-weight: 400;

            text-align: center;
        }

        /* TAGLINE */

        .tagline {
            margin-top: 24px;

            text-align: center;

            color: #555;

            font-size: 12px;

            line-height: 1.7;
        }

        .tagline .main {
            color: #333;

            font-size: 13px;

            font-weight: 600;
        }

        /* BUTTON AREA */

        .button-area {
            position: absolute;

            left: 24px;
            right: 24px;
            bottom: 28px;
        }

        .start-btn {
            width: 100%;
            height: 48px;

            border: none;
            border-radius: 9px;

            background: #36a842;

            color: #ffffff;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            box-shadow: 0 3px 7px rgba(54, 168, 66, 0.20);

            transition: 0.2s ease;
        }

        .start-btn:hover {
            background: #2d9238;
        }

        .start-btn:active {
            transform: scale(0.98);
        }

        /* MOBILE */

        @media (max-width: 420px) {

            body {
                background: #f8f9ed;
            }

            .app {
                width: 100vw;
                height: 100vh;

                border-radius: 0;

                box-shadow: none;
            }
        }

    </style>

</head>

<body>

<div class="app">

    <!-- BACKGROUND PATTERN -->

    <div class="pattern">

        <span>🥕</span>
        <span>🍎</span>
        <span>🥬</span>
        <span>🥛</span>

        <span>🍞</span>
        <span>🧀</span>
        <span>🍅</span>
        <span>🥕</span>

        <span>🥛</span>
        <span>🍎</span>
        <span>🍞</span>
        <span>🥬</span>

        <span>🍅</span>
        <span>🥕</span>
        <span>🧀</span>
        <span>🍎</span>

        <span>🥬</span>
        <span>🥛</span>
        <span>🍞</span>
        <span>🍅</span>

        <span>🍎</span>
        <span>🧀</span>
        <span>🥕</span>
        <span>🥬</span>

        <span>🥛</span>
        <span>🍅</span>
        <span>🍞</span>
        <span>🍎</span>

    </div>


    <div class="content">

        <!-- PANTRY ILLUSTRATION -->

        <div class="illustration">

            <svg viewBox="0 0 240 240"
                 xmlns="http://www.w3.org/2000/svg">

                <!-- Basket handle -->

                <path
                    d="M62 105
                       C62 48 178 48 178 105"
                    fill="none"
                    stroke="#8A5A2B"
                    stroke-width="9"
                    stroke-linecap="round"
                />

                <!-- Apple -->

                <circle
                    cx="67"
                    cy="92"
                    r="30"
                    fill="#F05A47"
                    stroke="#222"
                    stroke-width="5"
                />

                <path
                    d="M67 62 C65 51 72 45 82 43"
                    fill="none"
                    stroke="#222"
                    stroke-width="5"
                    stroke-linecap="round"
                />

                <path
                    d="M75 49 C84 42 94 47 98 54
                       C87 58 80 56 75 49Z"
                    fill="#43A047"
                    stroke="#222"
                    stroke-width="4"
                />

                <!-- Milk bottle -->

                <path
                    d="M112 60
                       L112 45
                       L128 45
                       L128 60
                       L139 72
                       L139 112
                       Q139 120 131 120
                       L109 120
                       Q101 120 101 112
                       L101 72
                       Z"
                    fill="#F5F5F0"
                    stroke="#222"
                    stroke-width="5"
                />

                <rect
                    x="111"
                    y="38"
                    width="18"
                    height="9"
                    rx="3"
                    fill="#3B9D46"
                    stroke="#222"
                    stroke-width="4"
                />

                <path
                    d="M106 88 L134 88"
                    stroke="#55AFC7"
                    stroke-width="6"
                />

                <!-- Carrot -->

                <path
                    d="M161 62
                       C184 63 193 78 185 96
                       L157 132
                       C151 140 140 133 144 124
                       L158 86
                       C161 78 157 68 161 62Z"
                    fill="#F47B32"
                    stroke="#222"
                    stroke-width="5"
                />

                <!-- Carrot leaves -->

                <path
                    d="M164 64
                       C151 54 158 40 171 47
                       C166 33 181 31 187 44
                       C195 35 205 45 198 58
                       C207 58 207 70 194 73
                       Z"
                    fill="#52A83D"
                    stroke="#222"
                    stroke-width="5"
                    stroke-linejoin="round"
                />

                <!-- Bread -->

                <path
                    d="M49 125
                       Q49 108 65 108
                       L92 108
                       Q108 108 108 125
                       L108 144
                       Q108 153 99 153
                       L58 153
                       Q49 153 49 144Z"
                    fill="#E8B96A"
                    stroke="#222"
                    stroke-width="5"
                />

                <path
                    d="M62 121 L62 139"
                    stroke="#C98E42"
                    stroke-width="4"
                    stroke-linecap="round"
                />

                <path
                    d="M78 121 L78 139"
                    stroke="#C98E42"
                    stroke-width="4"
                    stroke-linecap="round"
                />

                <path
                    d="M94 121 L94 139"
                    stroke="#C98E42"
                    stroke-width="4"
                    stroke-linecap="round"
                />

                <!-- Basket -->

                <path
                    d="M37 133
                       L203 133
                       L188 190
                       Q186 200 176 200
                       L64 200
                       Q54 200 52 190
                       Z"
                    fill="#D99A48"
                    stroke="#222"
                    stroke-width="6"
                    stroke-linejoin="round"
                />

                <!-- Basket strips -->

                <path
                    d="M50 148 L190 148"
                    stroke="#B97832"
                    stroke-width="5"
                />

                <path
                    d="M55 165 L185 165"
                    stroke="#B97832"
                    stroke-width="5"
                />

                <path
                    d="M61 183 L179 183"
                    stroke="#B97832"
                    stroke-width="5"
                />

            </svg>

        </div>


        <!-- TITLE -->

        <h1 class="brand">
            Smart Pantry
        </h1>


        <!-- SUBTITLE -->

        <p class="subtitle">
            Food Inventory &amp; Expiry Tracker
        </p>


        <!-- TAGLINE -->

        <div class="tagline">

            <div class="main">
                Track. Manage. Save.
            </div>

            <div>
                Reduce waste, Save money.
            </div>

        </div>


        <!-- GET STARTED -->

        <div class="button-area">

            <button
                class="start-btn"
                onclick="window.location.href='login.php'">

                Get Started

            </button>

        </div>

    </div>

</div>

</body>

</html>