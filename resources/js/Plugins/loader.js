import { ref } from 'vue';

export const isGlobalLoading = ref(false);
export const globalLoadingText = ref('MEMPROSES DATA');

export const showGlobalLoader = (text = 'MEMPROSES DATA') => {
  globalLoadingText.value = text;
  isGlobalLoading.value = true;
};

export const hideGlobalLoader = () => {
  isGlobalLoading.value = false;
};
