<?php
// Placeholder for typography controls
?>
<div>
    <h4>הגדרות טיפוגרפיה</h4>
    <!-- פקדי טיפוגרפיה יתווספו כאן -->
    <p>בקרוב...</p>
</div>
<?php
function render_typography_settings() {
    ?>
    <div class="typography-settings space-y-4">
        <!-- גופן -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">גופן</label>
            <select x-model="selectedElement.settings.typography.fontFamily"
                    @change="updateElement"
                    class="w-full h-[38px] px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <option value="'Noto Sans Hebrew', sans-serif">Noto Sans Hebrew</option>
                <option value="'Heebo', sans-serif">Heebo</option>
                <option value="'Rubik', sans-serif">Rubik</option>
                <option value="'Assistant', sans-serif">Assistant</option>
                <option value="'Varela Round', sans-serif">Varela Round</option>
            </select>
        </div>

        <!-- גודל טקסט -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">גודל טקסט</label>
            <div class="flex items-center gap-2">
                <input type="range" 
                       x-model="selectedElement.settings.typography.fontSize"
                       @input="updateElement"
                       min="12"
                       max="72"
                       class="flex-1"
                >
                <div class="w-16">
                    <input type="number"
                           x-model="selectedElement.settings.typography.fontSize"
                           @input="updateElement"
                           min="12"
                           max="72"
                           class="w-full h-[38px] px-2 py-1 text-sm text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
                <span class="text-sm text-gray-500">px</span>
            </div>
        </div>

        <!-- משקל גופן -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">משקל גופן</label>
            <div class="grid grid-cols-4 gap-1 bg-gray-100 p-1 rounded-lg">
                <button @click="selectedElement.settings.typography.fontWeight = 300; updateElement()"
                        :class="{ 'bg-white shadow-sm': selectedElement.settings.typography.fontWeight === 300 }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-50 transition-all">
                    דק
                </button>
                <button @click="selectedElement.settings.typography.fontWeight = 400; updateElement()"
                        :class="{ 'bg-white shadow-sm': selectedElement.settings.typography.fontWeight === 400 }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-50 transition-all">
                    רגיל
                </button>
                <button @click="selectedElement.settings.typography.fontWeight = 500; updateElement()"
                        :class="{ 'bg-white shadow-sm': selectedElement.settings.typography.fontWeight === 500 }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-50 transition-all">
                    בינוני
                </button>
                <button @click="selectedElement.settings.typography.fontWeight = 700; updateElement()"
                        :class="{ 'bg-white shadow-sm': selectedElement.settings.typography.fontWeight === 700 }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-50 transition-all">
                    מודגש
                </button>
            </div>
        </div>

        <!-- גובה שורה -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">גובה שורה</label>
            <div class="flex items-center gap-2">
                <input type="range" 
                       x-model="selectedElement.settings.typography.lineHeight"
                       @input="updateElement"
                       min="1"
                       max="2"
                       step="0.1"
                       class="flex-1"
                >
                <div class="w-16">
                    <input type="number"
                           x-model="selectedElement.settings.typography.lineHeight"
                           @input="updateElement"
                           min="1"
                           max="2"
                           step="0.1"
                           class="w-full h-[38px] px-2 py-1 text-sm text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
            </div>
        </div>

        <!-- ריווח אותיות -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ריווח אותיות</label>
            <div class="flex items-center gap-2">
                <input type="range" 
                       x-model="selectedElement.settings.typography.letterSpacing"
                       @input="updateElement"
                       min="-0.1"
                       max="0.5"
                       step="0.01"
                       class="flex-1"
                >
                <div class="w-16">
                    <input type="number"
                           x-model="selectedElement.settings.typography.letterSpacing"
                           @input="updateElement"
                           min="-0.1"
                           max="0.5"
                           step="0.01"
                           class="w-full h-[38px] px-2 py-1 text-sm text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
                <span class="text-sm text-gray-500">em</span>
            </div>
        </div>

        <!-- עיצוב טקסט -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">עיצוב טקסט</label>
            <div class="flex flex-wrap gap-1">
                <button @click="toggleTextDecoration('underline')"
                        :class="{ 'bg-primary-50 text-primary-600': selectedElement.settings.typography.textDecoration?.includes('underline') }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-100 transition-all">
                    <i class="ri-underline"></i>
                </button>
                <button @click="toggleTextDecoration('line-through')"
                        :class="{ 'bg-primary-50 text-primary-600': selectedElement.settings.typography.textDecoration?.includes('line-through') }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-100 transition-all">
                    <i class="ri-strikethrough"></i>
                </button>
                <button @click="selectedElement.settings.typography.textTransform = selectedElement.settings.typography.textTransform === 'uppercase' ? 'none' : 'uppercase'; updateElement()"
                        :class="{ 'bg-primary-50 text-primary-600': selectedElement.settings.typography.textTransform === 'uppercase' }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-100 transition-all">
                    <i class="ri-font-size"></i>
                </button>
                <button @click="selectedElement.settings.typography.fontStyle = selectedElement.settings.typography.fontStyle === 'italic' ? 'normal' : 'italic'; updateElement()"
                        :class="{ 'bg-primary-50 text-primary-600': selectedElement.settings.typography.fontStyle === 'italic' }"
                        class="px-3 py-2 text-sm rounded-lg text-gray-700 hover:bg-gray-100 transition-all">
                    <i class="ri-italic"></i>
                </button>
            </div>
        </div>
    </div>
    <style>

    </style>
    <?php
}
?> 