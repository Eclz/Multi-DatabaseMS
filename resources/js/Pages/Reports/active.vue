<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">
      <inertia-link class="text-indigo-400 hover:text-indigo-600" :href="route('organizations')">Reports</inertia-link>
      <span class="text-indigo-400 font-medium">/</span> Active Assets Per Client
    </h1>
    <div class="bg-white rounded-md shadow overflow-hidden max-w-3xl">
      <form>
        <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
           <select-input v-model="form.name" :error="form.errors.name" class="pr-6 pb-8 w-full lg:w-1/2" label="Client Name">
            <option :value="null" />
            <option v-for="setting in settings" :key="setting.id" :value="setting.dname">{{ setting.clientName}}</option>
          </select-input>
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-end items-center">
          <loading-button :loading="form.processing" class="btn-indigo"><a :href="'/asset/' + form.name" class="rounded-lg bg-grey-200 px-4 py-1">Print Invoice</a></loading-button>
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
  metaInfo: { title: 'Create Organization' },
  components: {
    LoadingButton,
    SelectInput,
    TextInput,
  },
  layout: Layout,
  remember: 'form',
  props: {
    settings:Object
  },
  data() {
    return {
      form: this.$inertia.form({
        name: null,
      }),
    }
  },
  methods: {
    store() {
      this.form.post(this.route('organizations.store'))
    },
  },
}
</script>
