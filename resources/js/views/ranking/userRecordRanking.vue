<script setup>
import { onMounted } from "vue";
import userRecordRankingTable from "../../components/ranking/userRecordRankingTable.vue";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useGetRecordRanking from "../../composables/ranking/useGetRecordRanking";

const { getLoginUser, loginUser } = useGetLoginUser();

// メニュー別ランキングを取得
const {
  rankingContents,
  compGetData,
  categoryContents,
  getRecords,
} = useGetRecordRanking();

onMounted(async () => {
  await getLoginUser();
  await getRecords(loginUser.value.id);
});
</script>

<template>
  <div>
    <p class="mx-auto mt-10 md:w-6/12 w-11/12 mb-5 font-bold md:text-center">
      メニュー別の最高重量、最高ボリュームを表示しています。
    </p>
    <template v-if="compGetData">
      <userRecordRankingTable
        :ranking_contents="rankingContents"
        :category_contents="categoryContents"
      />
    </template>
    <template v-else>
      <p class="mx-auto mt-10 md:w-6/12 w-11/12 mb-5 font-bold md:text-center">
        データ取得中です。しばらくお待ちください。
      </p>
    </template>
  </div>
</template>

