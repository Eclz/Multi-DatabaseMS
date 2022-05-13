<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">
      <inertia-link class="text-indigo-400 hover:text-indigo-600" :href="route('organizations')">Clients</inertia-link>
      <span class="text-indigo-400 font-medium">/</span>
      
      <!-- <span  class="text-green-600 font-medium" v-if="moment(form.email).format('MMMM Do YYYY, h:mm:ss a') > moment().format('MMMM Do YYYY, h:mm:ss a')">
        LICIENSE ACTIVE
      </span> -->
      <span class="text-red-600 font-medium">
       {{ form.name }} 
      </span>
    </h1>
    <trashed-message v-if="organization.deleted_at" class="mb-6" @restore="restore">
      This Client has been de-activated.
    </trashed-message>
    <div class="bg-white rounded-md shadow overflow-hidden max-w-10xl">
      <form @submit.prevent="update">
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
          <button v-if="!organization.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">De-activate Account</button>
                <!-- <icon name="printer" class="block w-6 h-6 fill-gray-400" /> -->
                <loading-button  class="btn-indigo ml-auto"><a :href="'/organizations/' + organization.id" class="rounded-lg bg-grey-200 px-4 py-1">Print Invoice</a></loading-button>
        </div>
      </form>
    </div>
    <div class="mt-6 bg-white rounded shadow overflow-x-auto">
    </div>
    <div class="bg-white rounded-md shadow overflow-hidden max-w-3xl">
      <form @submit.prevent="ActivateAsset">
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
          <!-- :error="active.errors.country" -->
          <select-input v-model="active.activity"  class="pr-2 pb-4 w-full lg:w-1/2" :error="active.errors.activity" label="Action">
            <option :value="null"/>
            <option value="1">Activate</option>
            <option value="0">De-activate</option>
            <option value="2">Renew</option>
          </select-input>
          <input type="hidden" v-model="active.selected" >
          
          <!-- <button v-if="!organization.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">De-activate Client</button> -->
          <loading-button :loading="process"  class="btn-indigo ml-auto" type="submit">Apply</loading-button>
        </div>
      </form>
    </div>

    

    <div class="mt-6 bg-white rounded shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="px-6 pt-6 pb-4">
            <label class="form-checkbox">
              <input type="checkbox" v-model="selectAll" @click="select">
              <i class="form-icon"></i>
            </label>
          </th>
          <th class="px-6 pt-6 pb-4">Asset</th>
          <th class="px-6 pt-6 pb-4">Status</th>
          <th class="px-6 pt-6 pb-4">Expiring Duration</th>
          <th class="px-6 pt-6 pb-4" colspan="2">Liciense End Date</th>
        </tr>
        <tr v-for="contact in organization.asset.data" :key="contact.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <!-- <inertia-link class="px-6 py-4 flex items-center focus:text-indigo-500" :href="route('contacts.edit', contact.id)"> -->
              <label class="px-6 py-4 flex items-center focus:text-indigo-500 form-checkbox">
                <input type="checkbox" :value="contact.id" v-model="active.selected">
                <i class="form-icon"></i>
              </label>
            <!-- </inertia-link> -->
          </td>
          <td class="border-t">
            <!-- <inertia-link class="px-6 py-4 flex items-center focus:text-indigo-500" :href="route('contacts.edit', contact.id)"> -->
              <div class="px-6 py-4 flex items-center focus:text-indigo-500">
                {{ contact.vehicle }}
                <icon v-if="contact.deleted_at" name="trash" class="flex-shrink-0 w-3 h-3 fill-gray-400 ml-2" />
              </div>
            <!-- </inertia-link> -->
          </td>
          <td class="border-t">
            <!-- <inertia-link class="px-6 py-4 flex items-center" :href="route('contacts.edit', contact.id)" tabindex="-1"> -->
              <div class="px-6 py-4 flex items-center focus:text-indigo-500">
                <div v-if="moment(contact.licienseto).format('YYYY-MM-DD') < moment().format('YYYY-MM-DD')" class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                    {{'De-activated'}}
                </div>
                <!-- <span class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">NEW</span> -->
                <div v-else-if="contact.setting == 4" class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-indigo-100 bg-indigo-600 rounded">
                    {{'New'}}
                </div>
                <div v-else class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none text-green-100 bg-green-600 rounded-full">
                    {{'Active'}}
                </div>
              </div>
            <!-- </inertia-link> -->
          </td>
           <td class="border-t">
              <div class="px-6 py-4 flex items-center focus:text-indigo-500">
            <!-- <inertia-link class="px-6 py-4 flex items-center" :href="route('contacts.edit', contact.id)" tabindex="-1"> -->
                  <div v-if="moment(contact.licienseto).format('MMMM Do YYYY, h:mm:ss a') > moment().format('MMMM Do YYYY, h:mm:ss a')" class="text-green-600 hover:underline">
                    {{ moment(contact.licienseto, "YYYYMMDD").fromNow()}}
                  </div>
                  <div v-else class="text-red-600 hover:underline">
                    {{ moment(contact.licienseto, "YYYYMMDD").fromNow()}}
                  </div>
              </div>
            <!-- </inertia-link> -->
          </td>
          <td class="border-t">
            <!-- <inertia-link class="px-6 py-4 flex items-center" :href="route('contacts.edit', contact.id)" tabindex="-1"> -->
              <div class="px-6 py-4 flex items-center focus:text-indigo-500">
                 {{ moment(contact.licienseto).format('MMMM Do YYYY, h:mm:ss a')}}
              </div>
            <!-- </inertia-link> -->
          </td>
          <td class="border-t w-px">
            <!-- <inertia-link class="px-4 flex items-center" :href="route('contacts.edit', contact.id)" tabindex="-1"> -->
            <div class="px-6 py-4 flex items-center focus:text-indigo-500">
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </div>
            <!-- </inertia-link> -->
          </td>
        </tr>
        <tr v-if="organization.asset.data.length === 0">
          <td class="border-t px-6 py-4" colspan="4">No assets found.</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :links="organization.asset.links" />
  </div>
</template>

<script>
import Icon from '@/Shared/Icon'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'
import TrashedMessage from '@/Shared/TrashedMessage'
import Pagination from '@/Shared/Pagination'




export default {
  metaInfo() {
    return { title: this.form.name }
  },
  components: {
    Icon,
    Pagination,
    LoadingButton,
    SelectInput,
    TextInput,
    TrashedMessage,
  },
  layout: Layout,
  props: {
    organization: Object,
    selections: Object,   
  },
  remember: 'form',
  data() {
    return {
      process: false,
      selectAll: false,
      form: this.$inertia.form({
        id: this.organization.id,
        dbname: this.organization.dname,
      }),
      active: this.$inertia.form({
        activity: null,
        name:this.organization.id,
        selected: [],
      }),
    }
  },

  methods: {
    select() {
			this.active.selected = [];
      // this.process = true
			if (!this.selectAll) {
        var arrayLength = this.organization.asset.data.length;
         for (var i = 0; i < arrayLength; i++) {
           this.active.selected.push(this.organization.asset.data[i].id);
          }   

			}
		},
    update() {
      // this.form.put(this.route('organizations.update', this.organization.id))
    },

    ActivateAsset() {
       this.active.post(this.route('organizations.activate'))
    },
    destroy() {
      if (confirm('Are you sure you want to delete this organization?')) {
        this.$inertia.delete(this.route('organizations.destroy', this.organization.id))
      }
    },
    restore() {
        this.$swal({
              title: 'Are you sure?',
              text: "You want to restore client!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, re-activate client!'
            }).then((result) => {
              if (result.value) {
                this.$swal(
                  'Restored!',
                  ''+ this.organization.name + '  has been Reactived.',
                  'success'
                )
                this.$inertia.put(this.route('organizations.restore', this.organization.id))

              }
          }
        );
      // if (confirm('Are you sure you want to restore this organization?')) {
      //   this.$inertia.put(this.route('organizations.restore', this.organization.id))
      // }
    },
  },
}
</script>
