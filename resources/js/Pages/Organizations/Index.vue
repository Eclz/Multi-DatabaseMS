<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">On boarded Cleints</h1>
    <div class="mb-6 flex justify-between items-center">
      <search-filter v-model="form.search" class="w-full max-w-md mr-4" @reset="reset">
        <label class="block text-gray-700">Trashed:</label>
        <select v-model="form.trashed" class="mt-1 w-full form-select">
          <option :value="null" />
          <option value="with">With Trashed</option>
          <option value="only">Only Trashed</option>
        </select>
      </search-filter>
      <inertia-link class="btn-indigo" :href="route('organizations.create')">
        <span>Create</span>
        <span class="hidden md:inline">Organization</span>
      </inertia-link>
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="px-6 pt-6 pb-4">Name</th>
          <th class="px-6 pt-6 pb-4">Assets</th>
          <th class="px-6 pt-6 pb-4">Heads</th>
          <th class="px-6 pt-6 pb-4">Trailers</th>
          <!-- <th class="px-6 pt-6 pb-4">Others</th> -->
          <th class="px-6 pt-6 pb-4" colspan="2">Asset Status</th>
        </tr>
        <tr v-for="organization in final.data" :key="organization.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <inertia-link class="px-6 py-4 flex items-center focus:text-indigo-500" :href="route('organizations.edit', organization.id)">
              {{ organization.clientName }}
              <icon v-if="organization.deleted_at" name="trash" class="flex-shrink-0 w-3 h-3 fill-gray-400 ml-2" />
            </inertia-link>
          </td>
          <td class="border-t">
            <inertia-link class="px-6 py-4 flex items-center" :href="route('organizations.edit', organization.id)" tabindex="-1">
              {{ organization.Heads + organization.others +organization.trailers }}
            </inertia-link>
          </td>
          <td class="border-t">
            <inertia-link class="px-6 py-4 flex items-center" :href="route('organizations.edit', organization.id)" tabindex="-1">
              {{ organization.Heads }}
            </inertia-link>
          </td>
          <td class="border-t">
            <inertia-link class="px-6 py-4 flex items-center" :href="route('organizations.edit', organization.id)" tabindex="-1">
              {{ organization.trailers }}
            </inertia-link>
          </td>
          <!-- <td class="border-t">
            <inertia-link class="px-6 py-4 flex items-center" :href="route('organizations.edit', organization.id)" tabindex="-1">
              {{ organization.others }}
            </inertia-link>
          </td> -->
          <td class="border-t w-px">
            <inertia-link v-if ="organization.new > 0" class="px-4 flex items-center inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-indigo-100 bg-indigo-600 rounded" :href="route('organizations.edit', organization.id)" tabindex="-1">
               {{ organization.new }} New
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </inertia-link>
            <inertia-link v-else class="px-4 flex items-center" :href="route('organizations.edit', organization.id)" tabindex="-1">
               <!-- {{ organization.new }} New -->
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </inertia-link>
          </td>
        </tr>
        <tr v-if="final.length === 0">
          <td class="border-t px-6 py-4" colspan="4">No organizations found.</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :links="final.links" />
  </div>
</template>

<script>
import Icon from '@/Shared/Icon'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination'
import SearchFilter from '@/Shared/SearchFilter'

export default {
  metaInfo: { title: 'Organizations' },
  components: {
    Icon,
    Pagination,
    SearchFilter,
  },
  layout: Layout,
  props: {
    filters: Object,
    // organizations: Object,
    final:Object,
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function() {
        this.$inertia.get(this.route('organizations'), pickBy(this.form), { preserveState: true })
      }, 150),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => null)
    },
  },
}
</script>
