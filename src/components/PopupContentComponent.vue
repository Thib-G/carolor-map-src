<template>
  <div>
    <div v-if="m">
      <h5>{{ m.name }}</h5>
      <button
        type="button"
        class="btn btn-light btn-sm"
        @click="showModal">Détail</button>
    </div>
  </div>
</template>

<script>
export default {
  props: ['m'],
  methods: {
    showModal() {
      this.$emit('showModal', this.m);
    },
  },
};
</script>
