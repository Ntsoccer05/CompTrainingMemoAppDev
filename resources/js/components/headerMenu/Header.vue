<template>
  <div class="bg-gray-500 h-12 md:h-16">
    <header class="container mx-auto text-white relative z-10">
      <div
        class="flex justify-between items-center fixed w-full bg-gray-500 px-2 h-16 md:h-16"
      >
        <template v-if="paramName === 'selectMenu'">
          <h3 class="md:mr-auto md:border-none">
            <router-link
              :to="
                recorded_day
                  ? { name: 'home', query: { day: recorded_day } }
                  : { name: 'home' }
              "
              class="md:text-xl font-semibold text-lg"
              ><i class="fa-solid fa-arrow-left mr-2"></i>日付選択へ戻る</router-link
            >
          </h3>
        </template>
        <template v-else-if="paramName === 'record' || paramName === 'addMenu'">
          <h3 class="md:mr-auto md:border-none">
            <router-link
              :to="{ name: 'selectMenu', params: { recordId: recorded_day } }"
              class="md:text-xl font-semibold text-lg"
              ><i class="fa-solid fa-arrow-left mr-2"></i>メニュー選択へ戻る</router-link
            >
          </h3>
        </template>
        <template v-else>
          <h3 class="md:mr-auto md:border-none">
            <router-link
              :to="
                recorded_day
                  ? { name: 'home', query: { day: recorded_day } }
                  : { name: 'home' }
              "
              class="text-xl font-semibold md:text-4xl"
              >トレメモ</router-link
            >
          </h3>
        </template>
        <div>
          <button @click="isOpen = !isOpen" class="focus:outline-none md:hidden">
            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
              <path
                v-show="!isOpen"
                d="M24 6h-24v-4h24v4zm0 4h-24v4h24v-4zm0 8h-24v4h24v-4z"
              />
              <path
                v-show="isOpen"
                d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"
              />
            </svg>
          </button>
        </div>
        <!-- ハンバーガーメニューの開閉(app.css) -->
        <div :class="isOpen ? 'show-toggleMenu' : 'hidden-toggleMenu'">
          <ul
            class="md:flex md:justify-between md:items-center md:mr-1 md: m-auto lg:mr-0"
            @click="closeHumbuger"
          >
            <template v-if="paramName === 'selectMenu' && !isOpen">
              <li class="border-b border-t top- md:mr-auto md:border-none">
                <router-link
                  :to="
                    recorded_day
                      ? { name: 'home', query: { day: recorded_day } }
                      : { name: 'home' }
                  "
                  class="block px-8 py-2 my-4 hover:bg-gray-600 rounded"
                  ><i class="fa-solid fa-arrow-left mr-2"></i>日付選択へ戻る</router-link
                >
              </li>
            </template>
            <template
              v-else-if="(paramName === 'record' || paramName === 'addMenu') && !isOpen"
            >
              <li class="border-b border-t top- md:mr-auto md:border-none">
                <router-link
                  :to="{ name: 'selectMenu', params: { recordId: recorded_day } }"
                  class="block px-8 py-2 my-4 hover:bg-gray-600 rounded"
                  ><i class="fa-solid fa-arrow-left mr-2"></i
                  >メニュー選択へ戻る</router-link
                >
              </li>
            </template>
            <template v-else>
              <li class="border-b border-t top- md:mr-auto md:border-none">
                <router-link
                  :to="
                    recorded_day
                      ? { name: 'home', query: { day: recorded_day } }
                      : { name: 'home' }
                  "
                  class="block px-8 py-2 my-4 hover:bg-gray-600 rounded"
                  >トレメモ</router-link
                >
              </li>
            </template>
            <template v-if="isLogined === true && isloaded">
              <li class="border-b md:border-none">
                <a
                  class="block px-8 py-2 my-4 hover:bg-gray-600 rounded cursor-pointer"
                  @click.native="logout"
                  >ログアウト</a
                >
              </li>
            </template>
            <template v-else-if="isLogined === false && isloaded">
              <li class="border-b md:border-none">
                <router-link
                  to="/login"
                  class="block px-8 py-2 my-4 hover:bg-gray-600 rounded"
                  >ログイン</router-link
                >
              </li>
              <li class="border-b md:border-none">
                <router-link
                  to="/register"
                  class="block px-8 py-2 my-4 hover:bg-gray-600 rounded"
                  >新規登録</router-link
                >
              </li>
            </template>
            <li>
              <div class="my-8 text-center md:my-4 md:mr-3">
                <router-link
                  to="/inquiry"
                  class="px-6 py-2 bg-orange-500 hover:bg-orange-400 rounded-full"
                  >お問い合わせ</router-link
                >
              </div>
            </li>
          </ul>
        </div>
      </div>
    </header>
  </div>
</template>
<script>
import { ref, onMounted, computed, watchEffect } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import useHoldLoginState from "../../composables/certification/useHoldLoginState";
export default {
  setup() {
    const router = useRouter();
    const route = useRoute();
    const store = useStore();

    // データ取得できたか
    const isloaded = ref(false);

    const recorded_day = ref("");
    const recordedAt = ref("");
    const recorded_at = computed(() => store.getters.getRecordedAt);

    const paramName = ref("");

    const isOpen = ref(false);
    const user = ref([]);

    // ログイン状態をVuexより取得
    const isLogined = computed(() => store.state.isLogined);

    //ちらつき防止のためログイン状態取得
    const { holdLoginState } = useHoldLoginState();

    watchEffect(() => {
      paramName.value = route.name;
      recorded_day.value = route.params.recordId;
      if (recorded_at) {
        recordedAt.value = recorded_at.value;
      }
    });

    // ハンバーガーメニューの表示/非表示
    const toggleNav = () => (isOpen.value = !isOpen.value);

    // ホーム画面へ遷移
    const toHome = () => router.push("/");

    const closeHumbuger = () => {
      if (isOpen) {
        isOpen.value = false;
      }
    };

    onMounted(async () => {
      //ちらつき防止のためログイン状態取得
      await holdLoginState();
      if (isLogined.value === false || isLogined.value === true) {
        isloaded.value = true;
      }
    });

    // ログアウト処理
    const logout = async () => {
      await axios
        .post("/api/logout", {})
        .then((res) => {
          if ((res.data.status_code = 200)) {
            // ログイン状態を変更するためVuexより呼び出し
            store.commit("LogoutState");
            //ページ再読み込み
            alert("ログアウトしました。");
            holdLoginState();
          }
        })
        .catch((err) => {
          console.log(err);
        });
    };
    return {
      paramName,
      isOpen,
      isLogined,
      isloaded,
      user,
      recorded_day,
      recordedAt,
      toggleNav,
      toHome,
      logout,
      closeHumbuger,
    };
  },
};
</script>

<style></style>
