define([
      'TYPO3/CMS/Mask/Contrib/vue',
    ],
    function (Vue) {
      return Vue.component(
          'splashscreen',
          {
            props: {
              loaded: Boolean,
              logoPath: String,
            },
            template: `
    <transition name="fade">
        <div v-show="!loaded" class="mask-splashscreen">
            <div class="half-circle-spinner">
                <div class="circle circle-1"></div>
                <div class="circle circle-2"></div>
            </div>
            <img :src="logoPath" class="mask-splashscreen__logo"/>
            <h1 class="mask-splashscreen__label">#StandWithUkraine</h1>
        </div>
    </transition>
        `
          }
      )
    }
);
