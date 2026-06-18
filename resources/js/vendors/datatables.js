import $ from './jquery';

import 'datatables.net-bs5';

import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.css';

import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';

import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

window.JSZip = JSZip;

pdfMake.vfs = pdfFonts.vfs;
window.pdfMake = pdfMake;

export default $;
