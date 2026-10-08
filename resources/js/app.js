import "./bootstrap";

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import imageUploader from "./image-uploader";

window.Alpine = Alpine;

Alpine.plugin(collapse);
Alpine.data("imageUploader", imageUploader);

Alpine.start();
