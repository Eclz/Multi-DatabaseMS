<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">Reports</h1>
    <div class="grid grid-cols-3 gap-4">

      <inertia-link class="max-w-xs overflow-hidden rounded-lg shadow-lg bg-blue-100"  :href="route('active')">
        <div class="px-12 py-6">
          <h4 class="mb-3 text-xl font-semibold tracking-tight text-gray-800">Active Assets</h4>
          <p class="leading-normal text-gray-700">This report is used to diplay </p>
           <!-- <loading-button  class="btn-indigo ml-auto"><a :href="'/organizations/'" class="rounded-lg bg-grey-200 px-4 py-1">Print Invoice</a></loading-button> -->
        </div>
      </inertia-link>
      <inertia-link :href="route('revenue')" class="max-w-xs overflow-hidden rounded-lg shadow-lg bg-blue-100">
        <div class="px-12 py-6">
          <h4 class="mb-3 text-xl font-semibold tracking-tight text-gray-800">Revenue</h4>
          <p class="leading-normal text-gray-700">Estimated Overroll Revenue Per Client.</p>
        </div>
      </inertia-link>
    </div>
  </div>
</template>

<script>
import Layout from '@/Shared/Layout'
import LoadingButton from '@/Shared/LoadingButton'

export default {
  metaInfo: { title: 'Reports' },
  layout: Layout,
  components: {
    LoadingButton,
  },
}
</script>
