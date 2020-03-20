<template>
  <AppHeaderDropdown class="nav-printer" right no-caret>
    <template slot="header" class="app-header-dropdown">
      <div class="printer-status">
        <i class="fa fa-circle" :class="dotClass"></i>
      </div>
      <img
        class="printer-icon"
        src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAALOSURBVGhD7ZjNaxNBGMb3PxAE/wmv4s1SxLrZJAfBevKmUmPVi6gUpAc92N1NrDbBjyIpmxYRvWhsRfBgiR/ZmegGBWtCVYRYPaiRVisN1eyO8yaTJpFBJYRklu4DzyHwzr6/h2TenYy07uQPo9ldo5knnTT0ZO3bJ5+GiD79hkykCh0x9IKerH37BA+dW/hOOiXo5QX5m7wgLcoL8i95QVpU24MoGtoGD+xWEDAwMJzW5cogylm8eadqHpNH0NCaNXSl9sDY/XfkJvrYEUOvWl9gaGQCRmBl2M1SdDREF9n9Uau0Lz630ugDE6/KA0aOhBKdNfSE3n/yACOw+nR8kuFXpejpHT4d2bFUkdx767jC0YdFAsyyjrezGDCRzBtHpvJl3gKRDcx0/1xnMSQpEMaZ4WSBWyyyT88skGA4g1mMSpCnbgxy5u4HAuwsBj9Ict4ht/K2UAamRsb/CmK8sMm4JZaBqZHRC9Jte0F4D+um10cQ+pov1g5nrjXNQL+NDDp67TUZTy+60sAeiGSQJKvmpUOJvNP4lbnJISPnyJp5EY7vh/tjWZtX5AYDu6yiQRrE7KUnSDI9X+YWiuwZylzdI7iH/it8tAk+TFo/uMUiO2EtV4LIo+bGyuTyh/HShdnP3GKRfZ4y0/H7tRICBLt+OPmeWyyy4d1Hp+5jFkOqTa5fvGKRHTLyPyl7lMWAIGhwTzRb4hWL7N1jVklW8UEWAy4fzF66aRw3Ta47lYllOk13XmuTK7vMXSSijWfVidWnWRtYjKr8Ol500+Q69+ATodP2C8OvKxDBppsm16nbBRKM4BTDr4ueV8YGjNwqb5GI3h9/uUqHlM7w6/KpZp+i4XIcf+MuFMlX0RJcbMOVaQ/Db1ZQxzF6iLT3Xn7edNcqkoHNpyLHr6MIw+ZLGUlvlTV8go42TUTTn9Nx+u7YwnA9efLkyVM3JUm/AXkT3UunOY/jAAAAAElFTkSuQmCC"
      />
      {{ printerDevice }} </template
    >\
    <template slot="dropdown">
      <b-dropdown-item
        v-if="printerStatus === 'CONNECTED'"
        v-for="(printer, i) in printerDevices"
        :key="i"
        @click="printerSetDevice(printer)"
        href="#"
        >{{ printer }}</b-dropdown-item
      >

      <b-dropdown-item
        v-if="printerStatus === 'DISCONNECTED'"
        @click="printerConnect()"
        href="#"
        >Retry connection</b-dropdown-item
      >
    </template>
  </AppHeaderDropdown>
</template>

<style lang="scss">
.nav-printer {
  margin-left: 30px !important;
  margin-right: 15px !important;
  position: relative;
  padding: 6px 14px;
  border-radius: 5px;

  .nav-link {
    font-size: 16px;
  }

  .printer-icon {
    width: 20px;
    margin-right: 3px;
  }

  a.dropdown-toggle {
    color: #434343 !important;
  }

  &.inactive {
    padding: 0;
    background: transparent;
    margin-left: 0 !important;

    .printer-icon {
      display: none;
    }

    a.dropdown-toggle {
      font-size: 0;
      margin-left: 0;
    }

    .printer-status {
      position: static;
      padding-right: 0;
    }
  }

  .printer-status {
    position: absolute;
    top: 0;
    right: 100%;
    bottom: 0;
    pointer-events: none;
    color: #fff;
    font-size: 1rem;
    height: 1.5em;
    margin: auto;
    padding-right: 15px;
  }
}
</style>

<script>
import { HeaderDropdown as AppHeaderDropdown } from "@coreui/vue";
import { mapGetters, mapActions } from "vuex";
import printer from "../mixins/printer";

export default {
  components: {
    AppHeaderDropdown
  },
  mixins: [printer],
  async mounted() {
    // BMP Testing
    if (this.viewedStore.id === 40) {
      await this.printerConnect();
      this.printerFindDevices();
    }
  },
  computed: {
    ...mapGetters(["viewedStore"]),
    dotClass() {
      switch (this.printerStatus) {
        case "CONNECTED":
          return "text-success";
        case "WAITING":
          return "text-warning";
        default:
          return "text-danger";
      }
    }
  },
  methods: {}
};
</script>

<style></style>
