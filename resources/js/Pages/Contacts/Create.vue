<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">
      <inertia-link class="text-indigo-400 hover:text-indigo-600" :href="route('contacts')">Billings</inertia-link>
      <span class="text-indigo-400 font-medium">/</span> Create
    </h1>
    <div class="bg-white rounded-md shadow overflow-hidden max-w-3xl">
      <form @submit.prevent="store">
        <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
          <select-input v-model="form.selection_id" :error="form.errors.selection_id" class="pr-6 pb-8 w-full lg:w-1/2" label="Organization">
            <option :value="null" />
            <option v-for="selection in selections" :key="selection.id" :value="selection.id">{{ selection.clientName }}</option>
          </select-input>
          <select-input v-model="form.package" :error="form.errors.package" class="pr-6 pb-8 w-full lg:w-1/2" label="Package">
            <option :value="null" />
            <option value="31">Monthly</option>
            <option value="122">Quarterly</option>
            <option value="365">Annually</option>
          </select-input>
          <text-input type="number" v-model="form.carbin" :error="form.errors.carbin" class="pr-6 pb-8 w-full lg:w-1/3" label="Rate Per Carbin Head" />
          <text-input type="number" v-model="form.trail" :error="form.errors.trail" class="pr-6 pb-8 w-full lg:w-1/3" label="Rate Per Carbin Tail" />
          <text-input v-model="form.currency" :error="form.errors.currency" class="pr-6 pb-8 w-full lg:w-1/3" label="Currency Symbole" />
          <text-input type="number" v-model="form.activate" :error="form.errors.activate" class="pr-6 pb-8 w-full lg:w-1/2" label="Activation Duration in Days" />
          <text-input type="number" v-model="form.deactivate" :error="form.errors.deactivate" class="pr-6 pb-8 w-full lg:w-1/2" label="De-activation Duration in Days" />
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end items-center">
          <loading-button :loading="form.processing" class="btn-indigo" type="submit">Create Billing</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'

export default {
  metaInfo: { title: 'Create Contact' },
  components: {
    LoadingButton,
    SelectInput,
    TextInput,
  },
  layout: Layout,
  props: {
    organizations: Array,
    selections: Object,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        selection_id: null,
        package: null,
        carbin: null,
        trail: null,
        currency:null,
        deactivate:null,
        activate:null
      }),

    }
  },
  methods: {
    store() {
      this.form.post(this.route('contacts.store'))
    },
  },
}
</script>
