<template>
  <div>
    <h1 class="mb-8 font-bold text-3xl">Dashboard</h1>
    <p class="mb-8 leading-normal"> </p>
    <div class="grid grid-cols-2 gap-2">
         <GChart
            type="PieChart"
            :options="options"
            :data="chartData"
          />

          <!-- <GChart
            type="PieChart"
            :options="options"
            :data="chartData"
          /> -->
    </div>

   
  
  </div>
</template>

<script>
import Layout from '@/Shared/Layout'
import { GChart } from "vue-google-charts";

export default {
  metaInfo: { title: 'Dashboard' },
  layout: Layout,
  components: {
    GChart,
  }
  ,
  props:{
   pie:Array

  },
  data() {
    return {
      chartData:this.pie,
      options: {
        height: 200
      }
    };
  }
  
}
</script>
