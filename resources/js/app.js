import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import './theme';
import Swal from 'sweetalert2'
import 'sweetalert2/src/sweetalert2.scss'
import 'quill/'
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import  bedMoveProps from './bedmove';
import './window-options'
import Quill from 'quill';
import flatpckr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';


import {
    Datepicker,
    Input,
    Select,
    Ripple,
    initTE,
    Collapse,
    Dropdown,
    Sidenav,
    Button,
    Modal,
    Timepicker,
    Tab,
    Stepper,
    Offcanvas
} from "tw-elements";

initTE({ Datepicker, Offcanvas, Select, Input, Ripple, Collapse, Dropdown, Sidenav, Button, Modal, Timepicker, Tab, Stepper });

window.flatpckr = flatpckr;

window.Tab = Tab;
window.Modal = Modal;
window.Stepper =Stepper;
window.Input = Input;
window.Datepicker = Datepicker;
window.Timepicker = Timepicker;
window.Swal = Swal;
window.Quill = Quill;
window.Select = Select;
window.Offcanvas = Offcanvas;
window.Button = Button;
window.Dropdown = Dropdown;
/* Load Nurse const for nurse ipd list*/
window.bedMoveProps = bedMoveProps;
//window.nurseListProps = nurseListProps;
//window.newCaseModal = newCaseModal;

const sidenav = document.getElementById("sidenav-main");
const sidenavInstance = Sidenav.getInstance(sidenav);

let innerWidth = null;

const setMode = (e) => {
  // Check necessary for Android devices
  if (window.innerWidth === innerWidth) {
    return;
  }

  innerWidth = window.innerWidth;

  if (window.innerWidth < sidenavInstance.getBreakpoint("sm")) {
    sidenavInstance.changeMode("over");
    sidenavInstance.hide();
  } else {
    sidenavInstance.changeMode("side");
    sidenavInstance.show();
  }
};

if (sidenavInstance && window.innerWidth < (sidenavInstance.getBreakpoint("sm"))) {
  setMode();
}

// Event listeners
window.addEventListener("resize", setMode);

window.addEventListener('toastify', event => {
    Swal.fire({
        title: 'Success',
        text: event.detail.text ?? 'Good job!',
        icon: 'success',
        showConfirmButton: false,
        toast: true,
        position: 'top',
        timer: 2500
    })
})

window.addEventListener('swal:error', event => {
    Swal.fire({
        icon: 'error',
        title: event.detail.title ?? 'Oops...',
        text: event.detail.text ?? 'Something went wrong!'
      })

})

window.addEventListener('delete:confirm', event => {
    Swal.fire({
        title: event.detail.title ?? 'คุณแน่ใจไหม?',
        text: event.detail.text ?? "คุณจะไม่สามารถย้อนกลับได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: event.detail.confirmButtonText ?? 'ใช่ ลบเลย!',
        cancelButtonText: event.detail.cancelButtonText ?? 'ไม่, ยกเลิก!',
        reverseButtons: true,
        allowOutsideClick: false,
    }).then((result) => {
        if (result.isConfirmed) {
            console.log(result)
			livewire.emit(event.detail.action)
		} else if (
			result.dismiss === Swal.DismissReason.cancel
		){}
    })
})

window.addEventListener('cat:progress', event => {
    Swal.fire({
        imageUrl: '/images/nyan/nyan-cat.gif',
        imageHeight: 250,
        title: window.dialogTitle.progress,
        width: 480,
        padding: '1.0em',
        color: '#db2777',
        background: '#fff',
        showConfirmButton: false,
        allowOutsideClick: false
        /*backdrop: `
        rgba(0,0,123,0.4)
        url("/images/nyan-cat.gif")
        left top
        no-repeat
        `*/
    })
})

window.addEventListener('swal:close', event => {
    Swal.close()
})

/*const wardSelect = document.getElementById('wardSelect');

wardSelect.addEventListener('valueChange.te.select', (e) => {

});*/

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('ipdViewMode', {
        value: localStorage.getItem('ipd-view-mode'),
        toggle() {
            this.value = this.value == 'grid' ? 'flex' : 'grid'
            localStorage.setItem('ipd-view-mode', this.value)
        }
    });
})

Alpine.plugin(focus);

Alpine.store('bed', {
    data: {id:'', name:'', wardId:''},
    set(val) {
        this.data = val
    }
})

Alpine.start();
