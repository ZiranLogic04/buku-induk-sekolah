<template>
  <transition name="toast">
    <div v-if="localShow" class="toast-wrapper" :class="type">
      <div class="toast-content">
        <div class="toast-icon">
          <span class="material-symbols-outlined">{{ icon }}</span>
        </div>
        <div class="toast-message">
          <p class="toast-title">{{ title }}</p>
          <p class="toast-desc">{{ message }}</p>
        </div>
        <button class="toast-close" @click="close">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="toast-progress">
        <div class="progress-bar" :style="{ width: progress + '%' }"></div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: Boolean,
  type: {
    type: String,
    default: 'success', // success, error, warning
    validator: (value) => ['success', 'error', 'warning'].includes(value)
  },
  title: String,
  message: String,
  duration: {
    type: Number,
    default: 3000
  }
});

const emit = defineEmits(['close']);

const localShow = ref(false);
const progress = ref(100);
let timer = null;
let progressInterval = null;

const icon = computed(() => {
  switch (props.type) {
    case 'success': return 'check_circle';
    case 'error': return 'error';
    case 'warning': return 'warning';
    default: return 'info';
  }
});

watch(() => props.show, (newVal) => {
  if (newVal) {
    localShow.value = true;
    startTimer();
  } else {
    localShow.value = false;
    clearTimer();
  }
});

const startTimer = () => {
    clearTimer();
    progress.value = 100;
    const step = 100 / (props.duration / 50); // 50ms interval updates
    
    progressInterval = setInterval(() => {
        progress.value -= step;
        if (progress.value <= 0) {
            close();
        }
    }, 50);

    timer = setTimeout(() => {
        close();
    }, props.duration);
};

const clearTimer = () => {
    if (timer) clearTimeout(timer);
    if (progressInterval) clearInterval(progressInterval);
};

const close = () => {
  localShow.value = false;
  clearTimer();
  setTimeout(() => {
      emit('close');
  }, 300); // Wait for transition
};

onUnmounted(() => {
    clearTimer();
});
</script>

<style scoped>
.toast-wrapper {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 9999;
  min-width: 320px;
  max-width: 400px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  border-radius: 1rem;
  box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.5);
}

.toast-wrapper.success { border-left: 4px solid #10b981; }
.toast-wrapper.success .toast-icon { color: #10b981; background: #d1fae5; }
.toast-wrapper.success .progress-bar { background: #10b981; }

.toast-wrapper.error { border-left: 4px solid #ef4444; }
.toast-wrapper.error .toast-icon { color: #ef4444; background: #fee2e2; }
.toast-wrapper.error .progress-bar { background: #ef4444; }

.toast-wrapper.warning { border-left: 4px solid #f59e0b; }
.toast-wrapper.warning .toast-icon { color: #f59e0b; background: #fef3c7; }
.toast-wrapper.warning .progress-bar { background: #f59e0b; }

.toast-content {
  padding: 1rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.toast-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-message {
  flex: 1;
}

.toast-title {
  font-weight: 700;
  font-size: 0.875rem;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.toast-desc {
  font-size: 0.8125rem;
  color: #64748b;
  line-height: 1.4;
}

.toast-close {
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.toast-close:hover { color: #1e293b; }

.toast-progress {
  height: 3px;
  background: #f1f5f9;
  width: 100%;
}

.progress-bar {
  height: 100%;
  transition: width 0.05s linear;
}

/* Transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}
</style>
