<template>
  <div v-if="isGlobalLoading" class="fullscreen-loader">
    <div class="spinner">
      <div class="ring-outer"></div>
      <div class="ring-middle"></div>
      <div class="icon">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <polygon points="24,6 44,15 24,24 4,15" fill="#1a4a8a" stroke="#4a90d9" stroke-width="1.8" stroke-linejoin="round"/>
          <path d="M12 19 L12 30 Q12 36 24 36 Q36 36 36 30 L36 19" fill="#1a4a8a" stroke="#4a90d9" stroke-width="1.8" stroke-linejoin="round"/>
          <line x1="44" y1="15" x2="44" y2="26" stroke="#4a90d9" stroke-width="1.8" stroke-linecap="round"/>
          <line x1="41" y1="26" x2="44" y2="26" stroke="#4a90d9" stroke-width="1.8" stroke-linecap="round"/>
          <line x1="44" y1="26" x2="47" y2="26" stroke="#4a90d9" stroke-width="1.8" stroke-linecap="round"/>
          <line x1="41" y1="26" x2="40" y2="30" stroke="#4a90d9" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="44" y1="26" x2="44" y2="31" stroke="#4a90d9" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="47" y1="26" x2="48" y2="30" stroke="#4a90d9" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="14" y1="22.5" x2="34" y2="22.5" stroke="rgba(74,144,217,0.35)" stroke-width="1" stroke-linecap="round"/>
        </svg>
      </div>
    </div>
    <p class="loading-text">{{ globalLoadingText }}<span class="loading-dots"></span></p>
  </div>
</template>

<script setup>
import { isGlobalLoading, globalLoadingText } from '@/Plugins/loader.js';
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500&display=swap');

.fullscreen-loader {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(11, 26, 46, 0.95);
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  font-family: 'DM Sans', sans-serif; gap: 20px;
}
.spinner { position: relative; width: 88px; height: 88px; }
.ring-outer {
  position: absolute; inset: 0; border-radius: 50%;
  border: 3px solid rgba(255,255,255,0.08); border-top-color: #4a90d9; border-right-color: #4a90d9;
  animation: spin-cw 1.5s cubic-bezier(0.55,0.055,0.675,0.19) infinite;
}
.ring-middle {
  position: absolute; inset: 11px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.05); border-bottom-color: #2159a0; border-left-color: #2159a0;
  animation: spin-ccw 1.1s linear infinite;
}
.icon {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  animation: pulse 2.2s ease-in-out infinite;
}
.icon svg { width: 34px; height: 34px; }
.loading-text {
  font-size: 0.75rem; font-weight: 500; color: #ffffff;
  letter-spacing: 0.18em; text-transform: uppercase;
}
.loading-dots::after {
  content: '';
  animation: dots 1.5s steps(4, end) infinite;
}
@keyframes dots {
  0% { content: ''; }
  25% { content: '.'; }
  50% { content: '..'; }
  75% { content: '...'; }
}
@keyframes spin-cw  { to { transform: rotate(360deg); } }
@keyframes spin-ccw { to { transform: rotate(-360deg); } }
@keyframes pulse {
  0%, 100% { opacity: 0.65; transform: scale(0.94); }
  50%       { opacity: 1;    transform: scale(1.06); }
}
</style>
