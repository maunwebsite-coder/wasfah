/**
 * @file recipe-save-button.js
 * @description نظام موحد لإدارة زر حفظ الوصفة في صفحة الوصفة الفردية
 * @version 2.0
 * @author Wasfah Team
 */

class RecipeSaveButton {
    constructor() {
        this.button = null;
        this.recipeId = null;
        this.isSaved = false;
        this.isProcessing = false;
        this.saveCountElement = null;
        
        // منع إضافة مستمعين متعددين
        if (document.getElementById('save-recipe-page-btn')?.dataset.listenerAttached === 'true') {
            console.log('Recipe save button already has listener attached');
            return;
        }
        
        this.init();
    }

    /**
     * تهيئة زر الحفظ
     */
    init() {
        // البحث عن زر الحفظ
        this.button = document.getElementById('save-recipe-page-btn');
        
        if (!this.button) {
            console.error('Recipe save button not found!');
            return;
        }

        // استخراج البيانات من الزر
        this.recipeId = parseInt(this.button.dataset.recipeId);
        this.isSaved = this.button.dataset.saved === 'true';
        
        // البحث عن عنصر العداد
        this.saveCountElement = document.getElementById('recipe-save-count');
        
        if (!this.recipeId) {
            console.error('Recipe ID not found!');
            return;
        }

        console.log('Initializing recipe save button:', {
            recipeId: this.recipeId,
            isSaved: this.isSaved
        });

        // تهيئة الحالة الأولية
        this.updateButtonState(this.isSaved);
        
        // إضافة مستمع الأحداث
        this.attachEventListener();
    }

    /**
     * تحديث حالة الزر بصرياً
     * @param {boolean} saved - حالة الحفظ
     */
    updateButtonState(saved) {
        if (!this.button) return;

        const span = this.button.querySelector('span');
        const icon = this.button.querySelector('i');

        // تنظيف الكلاسات السابقة
        this.button.classList.remove(
            'bg-orange-500', 'hover:bg-orange-600',
            'bg-green-500', 'hover:bg-green-600'
        );

        if (saved) {
            // حالة محفوظة - أخضر
            this.button.classList.add('bg-green-500', 'hover:bg-green-600');
            if (span) span.textContent = 'تم الحفظ';
            if (icon) icon.className = 'fas fa-bookmark ml-2';
        } else {
            // حالة غير محفوظة - برتقالي
            this.button.classList.add('bg-orange-500', 'hover:bg-orange-600');
            if (span) span.textContent = 'احفظ الوصفة';
            if (icon) icon.className = 'fas fa-bookmark ml-2';
        }

        // تحديث البيانات
        this.isSaved = saved;
        this.button.dataset.saved = saved.toString();
    }

    /**
     * تحديث عداد الحفظ
     * @param {boolean} saved - حالة الحفظ الجديدة
     */
    updateSaveCount(saved) {
        if (!this.saveCountElement) return;

        const currentText = this.saveCountElement.textContent;
        const currentCount = parseInt(currentText.match(/(\d+)/)?.[1] || '0');
        const newCount = saved ? currentCount + 1 : Math.max(0, currentCount - 1);
        
        this.saveCountElement.textContent = `${newCount} شخص حفظوا هذه الوصفة`;
        
        // تأثير بصري للتأكيد
        this.saveCountElement.style.color = '#0819ff';
        this.saveCountElement.style.fontWeight = 'bold';
        this.saveCountElement.style.transform = 'scale(1.05)';
        this.saveCountElement.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            this.saveCountElement.style.color = '';
            this.saveCountElement.style.fontWeight = '';
            this.saveCountElement.style.transform = 'scale(1)';
        }, 500);

        console.log(`Save count updated: ${currentCount} -> ${newCount}`);
    }

    /**
     * إضافة مستمع الأحداث للزر
     */
    attachEventListener() {
        if (!this.button) return;

        // منع إضافة مستمعين متعددين
        if (this.button.dataset.listenerAttached === 'true') {
            return;
        }

        this.button.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();
            
            await this.handleSaveAction();
        });

        // وضع علامة لتجنب إضافة مستمعين متعددين
        this.button.dataset.listenerAttached = 'true';
    }

    /**
     * معالجة عملية الحفظ/إلغاء الحفظ
     */
    async handleSaveAction() {
        // منع التفاعل المتعدد
        if (this.isProcessing) return;
        
        // التحقق من تسجيل الدخول
        const userId = document.body.dataset.userId || 
                      document.querySelector('[data-user-id]')?.dataset.userId;
        
        if (!userId || userId === 'null' || userId === '') {
            this.showToast('يجب تسجيل الدخول لحفظ الوصفة', 'warning');
            setTimeout(() => {
                window.location.href = '/login';
            }, 1500);
            return;
        }

        this.isProcessing = true;
        const newSavedState = !this.isSaved;
        
        console.log('Processing save action:', {
            oldState: this.isSaved,
            newState: newSavedState
        });

        // تحديث فوري للواجهة (بدون تحديث العداد)
        this.updateButtonState(newSavedState);
        
        // تعطيل الزر مؤقتاً
        this.setButtonLoading(true);

        try {
            // إرسال الطلب للخادم
            await this.sendSaveRequest(newSavedState);
            
            // إظهار رسالة نجاح
            const message = newSavedState ? 'تم حفظ الوصفة بنجاح!' : 'تم إلغاء حفظ الوصفة';
            this.showToast(message, 'success');
            
        } catch (error) {
            console.error('Error saving recipe:', error);
            
            // إعادة الحالة السابقة في حالة الخطأ
            this.updateButtonState(this.isSaved);
            
            this.showToast('حدث خطأ أثناء محاولة حفظ الوصفة', 'error');
            
        } finally {
            this.setButtonLoading(false);
            this.isProcessing = false;
        }
    }

    /**
     * إرسال طلب الحفظ للخادم
     * @param {boolean} saved - حالة الحفظ الجديدة
     */
    async sendSaveRequest(saved) {
        // تأكد من تهيئة كوكي CSRF
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });

        const csrfToken = this.getCsrfToken();
        
        const response = await fetch('/api/interactions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'include',
            body: JSON.stringify({
                recipe_id: this.recipeId,
                is_saved: saved,
            }),
        });

        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('يجب تسجيل الدخول لحفظ الوصفة');
            } else if (response.status === 419) {
                // إعادة تحميل الصفحة لتجديد CSRF token
                window.location.reload();
                return;
            } else {
                const errorText = await response.text();
                console.error('Server error:', errorText);
                throw new Error('حدث خطأ في الخادم');
            }
        }

        const data = await response.json();
        console.log('Save request successful:', data);
        
        // استخدام بيانات الاستجابة لتحديث الواجهة بدقة
        if (data && typeof data.is_saved !== 'undefined') {
            // تحديث حالة الزر بناءً على الاستجابة الفعلية
            this.updateButtonState(data.is_saved);
            // تحديث العداد بناءً على الاستجابة الفعلية
            this.updateSaveCount(data.is_saved);
        }
        
        return data;
    }

    /**
     * تبديل حالة التحميل للزر
     * @param {boolean} loading - حالة التحميل
     */
    setButtonLoading(loading) {
        if (!this.button) return;

        if (loading) {
            this.button.disabled = true;
            this.button.classList.add('opacity-70', 'cursor-not-allowed');
            
            const icon = this.button.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-spinner fa-spin ml-2';
            }
        } else {
            this.button.disabled = false;
            this.button.classList.remove('opacity-70', 'cursor-not-allowed');
            
            const icon = this.button.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-bookmark ml-2';
            }
        }
    }

    /**
     * الحصول على CSRF token
     * @returns {string} CSRF token
     */
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * عرض رسالة toast
     * @param {string} message - الرسالة
     * @param {string} type - نوع الرسالة (success, error, warning, info)
     */
    showToast(message, type = 'info') {
        // إنشاء عنصر toast
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-semibold transform translate-x-full transition-transform duration-300 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            type === 'warning' ? 'bg-yellow-500' :
            'bg-blue-500'
        }`;
        toast.textContent = message;
        
        // إضافة للصفحة
        document.body.appendChild(toast);
        
        // إظهار الرسالة
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // إخفاء الرسالة بعد 3 ثواني
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
}

function bootstrapRecipeSaveButton() {
    const pageButton = document.getElementById('save-recipe-page-btn');
    if (!pageButton) {
        return;
    }

    if (window.__recipeSaveButtonInitialized) {
        return;
    }

    console.log('Initializing Recipe Save Button system...');
    window.recipeSaveButton = new RecipeSaveButton();
    window.__recipeSaveButtonInitialized = true;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapRecipeSaveButton, { once: true });
} else {
    bootstrapRecipeSaveButton();
}

// تصدير للاستخدام العام إذا لزم الأمر
window.RecipeSaveButton = RecipeSaveButton;


