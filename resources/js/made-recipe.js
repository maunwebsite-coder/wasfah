/**
 * نظام إدارة زر "لقد جربتها!" - النسخة المبسطة
 */

// متغيرات عامة
let madeRecipeBtn = null;
let questionTextEl = null;
let madeItCountEl = null;

// تهيئة النظام عند تحميل الصفحة حتى لو تم تحميل الملف بعد اكتمال DOM
const runWhenReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

runWhenReady(initializeMadeRecipeSystem);

/**
 * تهيئة نظام "لقد جربتها!"
 */
function initializeMadeRecipeSystem() {
    // البحث عن العناصر
    madeRecipeBtn = document.getElementById('made-recipe-btn');
    questionTextEl = document.getElementById('question-text');
    madeItCountEl = document.getElementById('made-it-count');
    
    if (!madeRecipeBtn || !questionTextEl || !madeItCountEl) {
        console.log('Required elements not found');
        return;
    }
    
    // إضافة مستمع النقر
    madeRecipeBtn.addEventListener('click', handleMadeRecipeClick);
    
    // تحديث الحالة الأولية
    updateDisplay();
    
    console.log('Made recipe system initialized');
}

/**
 * التعامل مع النقر على زر "لقد جربتها!"
 */
async function handleMadeRecipeClick(event) {
    event.preventDefault();
    
    const recipeId = madeRecipeBtn.dataset.recipeId;
    const userId = madeRecipeBtn.dataset.userId;
    
    if (!userId) {
        showToast('يجب تسجيل الدخول أولاً', 'error');
        window.location.href = '/login';
        return;
    }
    
    if (!recipeId) {
        showToast('خطأ في معرف الوصفة', 'error');
        return;
    }
    
    const isCurrentlyMade = madeRecipeBtn.dataset.made === 'true';
    const newMadeState = !isCurrentlyMade;
    
    try {
        // تعطيل الزر أثناء التحميل
        madeRecipeBtn.disabled = true;
        madeRecipeBtn.classList.add('opacity-70');
        
        // تأكد من تهيئة كوكي CSRF
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
        
        // إرسال الطلب
        const csrf = getCsrfToken();
        const response = await fetch('/api/interactions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'include',
            body: JSON.stringify({
                recipe_id: parseInt(recipeId),
                is_made: newMadeState,
            }),
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            } else if (response.status === 419) {
                window.location.reload();
                return;
            } else {
                const errorText = await response.text();
                console.error('Error response:', errorText);
                showToast('حدث خطأ أثناء تحديث الحالة', 'error');
                return;
            }
        }
        
        // تحديث البيانات
        madeRecipeBtn.dataset.made = newMadeState ? 'true' : 'false';
        
        // تحديث العداد
        updateCount(newMadeState);
        
        // إظهار رسالة النجاح
        showToast(newMadeState ? 'رائع! تم تأكيد تجربة الوصفة! 👨‍🍳' : 'تم إلغاء تأكيد التجربة', 'success');
        
    } catch (error) {
        console.error('Error in made recipe handler:', error);
        showToast('حدث خطأ غير متوقع', 'error');
    } finally {
        // إعادة تفعيل الزر
        madeRecipeBtn.disabled = false;
        madeRecipeBtn.classList.remove('opacity-70');
    }
}

/**
 * تحديث العرض بناءً على الحالة الحالية
 */
function updateDisplay() {
    const isMade = madeRecipeBtn.dataset.made === 'true';
    const currentCount = getCurrentCount();
    
    // تحديث الزر
    updateButton(isMade);
    
    // تحديث النص
    updateText(isMade, currentCount);
}

/**
 * تحديث شكل الزر ونصه
 */
function updateButton(isMade) {
    if (!madeRecipeBtn) {
        return;
    }

    const btnText = madeRecipeBtn.querySelector('#made-btn-text');
    const defaultText = madeRecipeBtn.dataset.defaultText || btnText?.textContent || 'لقد جربتها!';
    const activeText = madeRecipeBtn.dataset.activeText || defaultText;

    madeRecipeBtn.classList.remove('bg-orange-500', 'bg-green-500', 'hover:bg-orange-600', 'hover:bg-green-600');

    if (isMade) {
        madeRecipeBtn.classList.add('bg-green-500', 'hover:bg-green-600');
        if (btnText) {
            btnText.textContent = activeText;
        }
    } else {
        madeRecipeBtn.classList.add('bg-orange-500', 'hover:bg-orange-600');
        if (btnText) {
            btnText.textContent = defaultText;
        }
    }
}

/**
 * تحديث العداد عند تغيير الحالة
 */
function updateCount(isMade) {
    const currentCount = getCurrentCount();
    const newCount = isMade ? currentCount + 1 : Math.max(0, currentCount - 1);
    
    updateButton(isMade);
    
    // تحديث النص فورياً مع العدد الجديد
    updateText(isMade, newCount);
}

/**
 * تحديث النص
 */
function updateText(isMade, count) {
    if (isMade) {
        // إذا قام المستخدم بتحضير الوصفة
        questionTextEl.textContent = 'رائع! جرّبت هذا الدليل 👨‍🍳';
        questionTextEl.className = 'font-semibold text-green-700 text-lg';
        
        // تحديث العداد - إزالة كلمة "أنت" وجعل العداد يزيد +1
        if (count > 1) {
            madeItCountEl.innerHTML = `<span class="font-bold text-green-600 text-lg">${count}</span> جرّبوا هذا الدليل!`;
        } else {
            madeItCountEl.innerHTML = `أول من جرّب هذا الدليل! 🏆`;
        }
    } else {
        // إذا لم يقم المستخدم بتحضير الوصفة
        questionTextEl.textContent = 'هل جرّبت هذا الدليل؟';
        questionTextEl.className = 'font-semibold text-slate-900 text-lg';
        
        // تحديث العداد
        if (count > 0) {
            madeItCountEl.innerHTML = `<span class="font-bold text-orange-500 text-lg">${count}</span> جرّبوا هذا الدليل!`;
        } else {
            madeItCountEl.innerHTML = `كن أول من يجرّب هذا الدليل! 🚀`;
        }
    }
}

/**
 * الحصول على العدد الحالي من النص
 */
function getCurrentCount() {
    const countText = madeItCountEl.textContent;
    
    // إذا كان النص يحتوي على "كن أول من يجرب" فهذا يعني العدد 0
    if (countText.includes('كن أول من يجرب')) {
        return 0;
    }
    
    // إذا كان النص يحتوي على "أول من جرب" فهذا يعني العدد 1
    if (countText.includes('أول من جرب')) {
        return 1;
    }
    
    // البحث عن رقم في النص
    const match = countText.match(/(\d+)/);
    return match ? parseInt(match[1]) : 0;
}

/**
 * الحصول على CSRF token
 */
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

/**
 * إظهار رسالة toast
 */
function showToast(message, type = 'success') {
    // إنشاء عنصر toast
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium shadow-lg transform translate-x-full transition-transform duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
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
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// تصدير الدوال للاستخدام العام
window.MadeRecipe = {
    initializeMadeRecipeSystem,
    handleMadeRecipeClick,
    updateDisplay
};
