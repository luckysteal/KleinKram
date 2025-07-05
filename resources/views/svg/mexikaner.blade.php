<svg
  width="100"
  height="140"
  viewBox="0 0 100 140"
  fill="none"
  xmlns="http://www.w3.org/2000/svg"
>
  <!-- Shot glass outline -->
  <path
    d="M30 120 L70 120 Q75 120 75 110 L75 40 Q75 30 65 30 L35 30 Q25 30 25 40 L25 110 Q25 120 30 120 Z"
    fill="url(#glassGradient)"
    stroke="#999"
    stroke-width="2"
  />
  <!-- Glass inner highlight -->
  <path
    d="M35 35 L65 35 Q70 35 70 45 L70 105 Q70 115 65 115 L35 115 Q30 115 30 105 L30 45 Q30 35 35 35 Z"
    fill="url(#glassInnerGradient)"
  />
  <!-- Liquid -->
  <path
    d="M30 110 L70 110 Q72 110 72 100 L72 50 Q72 40 65 40 L35 40 Q28 40 28 50 L28 100 Q28 110 30 110 Z"
    fill="url(#liquidGradient)"
  />
  <!-- Liquid shine -->
  <path
    d="M40 50 Q45 30 60 50"
    stroke="white"
    stroke-width="3"
    fill="none"
    opacity="0.3"
  />
  <!-- Chili pepper on top -->
  <g transform="translate(50, 25) rotate(-15)">
    <!-- Pepper body -->
    <ellipse cx="0" cy="0" rx="30" ry="7" fill="#E53935" stroke="#B71C1C" stroke-width="2" />
    <path d="M-25 0 Q0 -15 25 0" fill="#B71C1C" />
    <!-- Stem positioned at right corner -->
    <line x1="25" y1="0" x2="35" y2="-10" stroke="#388E3C" stroke-width="3" stroke-linecap="round" />
  </g>
  <defs>
    <linearGradient id="glassGradient" x1="0" y1="30" x2="0" y2="120" gradientUnits="userSpaceOnUse">
      <stop offset="0%" stop-color="#e0e0e0" stop-opacity="0.8" />
      <stop offset="100%" stop-color="#b0b0b0" stop-opacity="0.3" />
    </linearGradient>
    <linearGradient id="glassInnerGradient" x1="0" y1="35" x2="0" y2="115" gradientUnits="userSpaceOnUse">
      <stop offset="0%" stop-color="white" stop-opacity="0.6" />
      <stop offset="100%" stop-color="white" stop-opacity="0" />
    </linearGradient>
    <linearGradient id="liquidGradient" x1="0" y1="40" x2="0" y2="110" gradientUnits="userSpaceOnUse">
      <stop offset="0%" stop-color="#FF6F3C" />
      <stop offset="100%" stop-color="#FF3D00" />
    </linearGradient>
  </defs>
</svg>