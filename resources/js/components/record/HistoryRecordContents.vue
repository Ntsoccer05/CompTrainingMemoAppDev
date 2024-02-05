<template>
  <div>
    <div class="text-center">最大過去５件を表示しています。</div>
    <template v-for="(historyMenu, index) in historyMenus" :key="historyMenu.id">
      <table class="border border-collapse table-fixed mx-auto mt-5">
        <thead>
          <tr>
            <th class="text-center bg-green-400 indent-1 md:indent-0">
              <div class="border" ref="todayRecordedAt">
                {{ historyMenu.recorded_at }}の記録
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="historyRecord in historyRecords[index]" :key="historyRecord.id">
            <td>
              <div class="bg-gray-200 border indent-1">
                {{ historyRecord.set }}セット目
              </div>
              <div :class="hasOneHand ? 'hidden' : 'block'">
                <!-- readonlyだとfocusできるが、disabledだとfocusもできない -->
                <!-- inputのvalue値にデータを入力するにはv-bindを用いる -->
                <input
                  class="border w-full"
                  type="text"
                  placeholder="重さ(kg)"
                  :value="historyRecord.weight"
                  disabled
                />
                <input
                  class="border w-full"
                  type="text"
                  placeholder="回数"
                  :value="historyRecord.rep"
                  disabled
                />
              </div>
              <div :class="hasOneHand ? 'block' : 'hidden'">
                <input
                  class="border w-full"
                  type="text"
                  placeholder="重さ（右）(kg)"
                  :value="historyRecord.right_weight"
                  disabled
                />
                <input
                  class="border w-full"
                  type="text"
                  placeholder="回数（右）"
                  :value="historyRecord.right_rep"
                  disabled
                />
                <input
                  class="border w-full"
                  type="text"
                  placeholder="重さ（左）(kg)"
                  :value="historyRecord.left_weight"
                  disabled
                />
                <input
                  class="border w-full"
                  type="text"
                  placeholder="回数（左）"
                  :value="historyRecord.left_rep"
                  disabled
                />
              </div>
              <div class="border">
                <textarea
                  class="w-full leading-4 pl-0.5"
                  name=""
                  id=""
                  cols="20"
                  rows="4"
                  placeholder="メモ"
                  :value="historyRecord.memo"
                  disabled
                ></textarea>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, watchEffect } from "vue";
import { useRoute } from "vue-router";
import useGetLoginUser from "../../composables/certification/useGetLoginUser";
import useGetTgtRecordContent from "../../composables/record/useGetTgtRecordContent.js";
import axios from "axios";
import { useStore } from "vuex";
const props = defineProps({
  historyMenus: [Object, String],
  historyRecords: [Object, String],
  hasHistoryRecord: Boolean,
  hasOneHand: Boolean,
});

const historyMenus = computed(() => props.historyMenus);
const historyRecords = computed(() => props.historyRecords);
const hasHistoryRecord = computed(() => props.hasHistoryRecord);
const hasOneHand = computed(() => props.hasOneHand);

</script>

<style></style>

