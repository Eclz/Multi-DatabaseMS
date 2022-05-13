<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">
      <inertia-link class="text-indigo-400 hover:text-indigo-600" :href="route('contacts')">Billing Category</inertia-link>
      <span class="text-indigo-400 font-medium">/</span>
      {{ form.selection_name }}
    </h1>
    <trashed-message v-if="contact.deleted_at" class="mb-6" @restore="restore">
      This billing plan has been deleted.
    </trashed-message>
    <div class="bg-white rounded-md shadow overflow-hidden max-w-3xl">
      <form @submit.prevent="update">
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
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
          <button v-if="!contact.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">Delete Contact</button>
          <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">Update Billing</loading-button>
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
import TrashedMessage from '@/Shared/TrashedMessage'

export default {
  metaInfo() {
    return {
      title: `${this.form.selection_name}`,
    }
  },
  components: {
    LoadingButton,
    SelectInput,
    TextInput,
    TrashedMessage,
  },
  layout: Layout,
  props: {
    contact: Object,
    selections: Object,
    organizations: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        selection_id: this.contact.selection_id,
        selection_name: this.contact.selection_name,
        package: this.contact.package,
        carbin: this.contact.carbin,
        trail: this.contact.trail,
        currency:this.contact.currency,
        deactivate:this.contact.deactivate,
        activate:this.contact.activate,
      }),
    }
  },
  methods: {
    update() {
      this.form.put(this.route('contacts.update', this.contact.id))
    },
    destroy() {
      this.$swal({
              title: 'Are you sure?',
              text: "You want to trash Billing!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, trash Billing!'
            }).then((result) => {
              if (result.value) {
                this.$swal(
                  'Trashed!',
                  ''+ this.contact.selection_name + '  has been trashed.',
                  'success'
                )
                this.$inertia.delete(this.route('contacts.destroy', this.contact.id))

              }
          }
        );
    },
    restore() {

      this.$swal({
              title: 'Are you sure?',
              text: "You want to restore this Billing!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, restore Billing!'
            }).then((result) => {
              if (result.value) {
                this.$swal(
                  'Restored!',
                  ''+ this.contact.selection_name + '  has been restored.',
                  'success'
                )
                this.$inertia.put(this.route('contacts.restore', this.contact.id))

              }
          }
        );
    },
  },
}
</script>
