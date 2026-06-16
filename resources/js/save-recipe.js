/**
 * @file save-recipe.js
 * @description ملف مشترك لإدارة جميع أزرار حفظ الوصفة في الموقع
 */

/**
 * يعرض رسالة toast للمستخدم
 * @param {string} message - الرسالة المراد عرضها
 * @param {string} type - نوع الرسالة (success, error, info)
 */
function showToast(message, type = 'info') {
    // إنشاء عنصر toast
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-semibold transform translate-x-full transition-transform duration-300 ${
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
            toast.remove();
        }, 300);
    }, 3000);
}

/**
 * يحصل على CSRF token
 * @returns {string} CSRF token
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * يحدث حالة زر الحفظ
 * @param {HTMLElement} button - عنصر الزر
 * @param {boolean} isSaved - حالة الحفظ
 */
function updateSaveButtonState(button, isSaved) {
    const span = button.querySelector('span');
    const icon = button.querySelector('i');
    const svg = button.querySelector('svg');
    
    // إزالة جميع الأنماط السابقة
    button.classList.remove(
        'bg-orange-500', 'hover:bg-orange-600', 
        'bg-green-500', 'hover:bg-green-600', 
        'bg-gray-100', 'hover:bg-gray-200', 'bg-gray-200', 'hover:bg-gray-300', 
        'text-gray-700', 'text-gray-800', 'text-white',
        'bg-orange-500/80', 'hover:bg-orange-600/80', 'hover:bg-orange-600/90',
        'bg-green-500/80', 'hover:bg-green-600/90',
        'bg-white/20', 'hover:bg-white/30'
    );
    
    if (isSaved) {
        // حالة محفوظة - أخضر
        if (span) span.textContent = 'تم الحفظ';
        if (icon) icon.className = 'fas fa-bookmark ml-1';
        
        if (button.closest('.card-front')) {
            // زر في المقدمة - أخضر شفاف
            button.classList.add('bg-green-500/80', 'hover:bg-green-600/90', 'text-white');
        } else if (button.closest('.card-back')) {
            // زر في الخلف - أخضر عادي
            button.classList.add('bg-green-500', 'hover:bg-green-600', 'text-white');
        } else {
            // زر عادي - أخضر
            button.classList.add('bg-green-500', 'hover:bg-green-600', 'text-white');
        }
        button.dataset.saved = 'true';
    } else {
        // حالة غير محفوظة - برتقالي
        if (span) span.textContent = 'حفظ';
        if (icon) icon.className = 'fas fa-bookmark ml-1';
        
        if (button.closest('.card-front')) {
            // زر في المقدمة - برتقالي شفاف
            button.classList.add('bg-orange-500/80', 'hover:bg-orange-600/90', 'text-white');
        } else if (button.closest('.card-back')) {
            // زر في الخلف - برتقالي
            button.classList.add('bg-orange-500', 'hover:bg-orange-600', 'text-white');
        } else {
            // زر عادي - برتقالي
            button.classList.add('bg-orange-500', 'hover:bg-orange-600', 'text-white');
        }
        button.dataset.saved = 'false';
    }
}

/**
 * يتعامل مع عملية حفظ/إلغاء حفظ الوصفة
 * @param {HTMLElement} button - عنصر الزر
 * @param {number} recipeId - معرف الوصفة
 */
async function handleSaveRecipe(button, recipeId) {
    const isCurrentlySaved = button.dataset.saved === 'true';
    const newSavedState = !isCurrentlySaved;

    // التحقق من تسجيل الدخول قبل المتابعة
    const userId = document.body.dataset.userId || document.querySelector('[data-user-id]')?.dataset.userId;
    if (!userId || userId === 'null' || userId === '') {
        showToast('يجب تسجيل الدخول لحفظ الوصفة', 'warning');
        window.location.href = '/login';
        return;
    }

    // تحديث فوري فوري لجميع أزرار الحفظ في نفس الكرت
    const cardContainer = button.closest('.card-container');
    if (cardContainer) {
        const allSaveButtonsInCard = cardContainer.querySelectorAll('.save-btn, .save-recipe-btn:not(#save-recipe-page-btn)');
        allSaveButtonsInCard.forEach(btn => {
            updateSaveButtonState(btn, newSavedState);
            btn.dataset.saved = newSavedState.toString();
            
            // إضافة تأثير بصري للتأكيد على التزامن
            btn.style.transform = 'scale(1.05)';
            btn.style.transition = 'transform 0.2s ease';
            setTimeout(() => {
                btn.style.transform = 'scale(1)';
            }, 200);
        });
    }

    // تحديث جميع أزرار الحفظ في الصفحة بنفس recipe_id فوراً - استثناء زر صفحة الوصفة
    const allSaveButtons = document.querySelectorAll('.save-recipe-btn:not(#save-recipe-page-btn), .save-btn');
    allSaveButtons.forEach(btn => {
        if (btn.dataset.recipeId === recipeId.toString()) {
            updateSaveButtonState(btn, newSavedState);
            btn.dataset.saved = newSavedState.toString();
            
            // إضافة تأثير بصري للتأكيد على التزامن
            btn.style.transform = 'scale(1.05)';
            btn.style.transition = 'transform 0.2s ease';
            setTimeout(() => {
                btn.style.transform = 'scale(1)';
            }, 200);
        }
    });

    // تحديث العداد فوراً مرة واحدة فقط (فقط إذا لم نكن في صفحة الوصفة)
    if (!document.getElementById('save-recipe-page-btn')) {
        updateRecipePageSaveCountImmediate(newSavedState);
    }

    try {
        
        // تعطيل الزر أثناء التحميل
        button.disabled = true;
        button.classList.add('opacity-70');

        // تأكد من تهيئة كوكي CSRF
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });

        const csrf = getCsrfToken();
        const res = await fetch('/api/interactions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'include',
            body: JSON.stringify({
                recipe_id: recipeId,
                is_saved: newSavedState,
            }),
        });

        if (!res.ok) {
            if (res.status === 401) {
                // المستخدم غير مسجل - لا نحدث العداد
                showToast('يجب تسجيل الدخول لحفظ الوصفة', 'warning');
                window.location.href = '/login';
                return;
            } else if (res.status === 419) {
                // إعادة تحميل الصفحة لتجديد CSRF token
                window.location.reload();
                return;
            } else {
                const txt = await res.text();
                console.error('Error body:', txt);
                showToast('حدث خطأ أثناء محاولة حفظ الوصفة. يرجى المحاولة مرة أخرى.', 'error');
                return;
            }
        }

        // تحليل الاستجابة واستخدام البيانات لتحديث الواجهة
        const responseData = await res.json();
        console.log('Save request successful:', responseData);
        
        // استخدام بيانات الاستجابة لتحديث الواجهة بدقة
        const finalSavedState = responseData && typeof responseData.is_saved !== 'undefined'
            ? Boolean(responseData.is_saved)
            : newSavedState;

        if (responseData && typeof responseData.is_saved !== 'undefined') {
            // تحديث جميع أزرار الحفظ في الصفحة بناءً على الاستجابة الفعلية
            const allSaveButtons = document.querySelectorAll('.save-recipe-btn:not(#save-recipe-page-btn), .save-btn');
            allSaveButtons.forEach(btn => {
                if (btn.dataset.recipeId === recipeId.toString()) {
                    updateSaveButtonState(btn, responseData.is_saved);
                    btn.dataset.saved = responseData.is_saved.toString();
                }
            });
            
        }
        const delta = finalSavedState === isCurrentlySaved ? 0 : (finalSavedState ? 1 : -1);
        if (delta !== 0 && typeof window !== 'undefined' && typeof window.dispatchEvent === 'function') {
            window.dispatchEvent(new CustomEvent('saved-counter:changed', {
                detail: { delta }
            }));
        }
        
        // إظهار رسالة نجاح
        showToast(finalSavedState ? 'تم حفظ الوصفة بنجاح!' : 'تم إلغاء حفظ الوصفة', 'success');

    } catch (err) {
        console.error(err);
        showToast('حدث خطأ أثناء محاولة حفظ الوصفة.', 'error');
        
        // إعادة العداد إلى حالته الأصلية عند حدوث خطأ (فقط إذا لم نكن في صفحة الوصفة)
        if (!document.getElementById('save-recipe-page-btn')) {
            updateRecipePageSaveCountImmediate(!newSavedState);
        }
    } finally {
        // إعادة تفعيل الزر
        button.disabled = false;
        button.classList.remove('opacity-70');
    }
}

/**
 * يهيئ جميع أزرار حفظ الوصفة في الصفحة
 */
function initializeSaveButtons() {
    // البحث عن جميع أزرار حفظ الوصفة (كلا النوعين) - استثناء زر صفحة الوصفة
    const saveButtons = document.querySelectorAll('.save-recipe-btn:not(#save-recipe-page-btn), .save-btn');
    
    saveButtons.forEach((button) => {
        // منع إضافة مستمعين متعددين
        if (button.dataset.initialized === 'true') {
            return;
        }
        
        // البحث عن معرف الوصفة في العنصر أو العنصر الأب
        let recipeId = button.dataset.recipeId;
        if (!recipeId) {
            const cardContainer = button.closest('.card-container');
            if (cardContainer) {
                recipeId = cardContainer.dataset.recipeId;
            }
        }
        
        if (!recipeId) {
            return;
        }

        // إضافة معرف الوصفة للزر إذا لم يكن موجوداً
        if (!button.dataset.recipeId) {
            button.dataset.recipeId = recipeId;
        }

        // تهيئة الحالة الأولية
        const isSaved = button.dataset.saved === 'true' || 
                       button.classList.contains('bg-green-500') ||
                       button.classList.contains('bg-green-600') ||
                       (button.classList.contains('bg-green-500/80') && 
                        button.classList.contains('text-white'));
        updateSaveButtonState(button, isSaved);
        button.dataset.saved = isSaved.toString();

        // منع إضافة مستمعين متعددين
        if (button.dataset.listenerAttached === 'true') {
            return;
        }

        // إضافة مستمع النقر
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation(); // منع انتشار الحدث إلى الكارت
            e.stopImmediatePropagation(); // منع معالجة الحدث من قبل مستمعين آخرين
            await handleSaveRecipe(button, parseInt(recipeId));
        });

        // وضع علامة لتجنب إضافة مستمعين متعددين
        button.dataset.listenerAttached = 'true';
        
        // تحديد أن الزر تم تهيئته
        button.dataset.initialized = 'true';
    });
}

/**
 * يهيئ زر حفظ الوصفة مع الحالة الأولية
 * @param {HTMLElement} button - عنصر الزر
 * @param {boolean} isSaved - حالة الحفظ الأولية
 */
function initializeSaveButtonWithState(button, isSaved) {
    if (!button) return;
    
    // منع إضافة مستمعين متعددين
    if (button.dataset.initialized === 'true') {
        // تحديث الحالة فقط
        updateSaveButtonState(button, isSaved);
        button.dataset.saved = isSaved.toString();
        return;
    }
    
    // تحديث الحالة الأولية
    updateSaveButtonState(button, isSaved);
    button.dataset.saved = isSaved.toString();
    
    // تهيئة الزر
    const recipeId = button.dataset.recipeId;
    if (recipeId) {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation(); // منع انتشار الحدث إلى الكارت
            e.stopImmediatePropagation(); // منع معالجة الحدث من قبل مستمعين آخرين
            await handleSaveRecipe(button, parseInt(recipeId));
        });
        button.dataset.initialized = 'true';
    }
}

const bootstrapState = {
    attempts: 0,
    maxAttempts: 3,
};

function hasSaveRecipeTargets() {
    return Boolean(
        document.querySelector('.save-btn, .save-recipe-btn, #save-recipe-page-btn, [data-save-button]') ||
            document.getElementById('recipeCards') ||
            document.querySelector('.card-container'),
    );
}

function bootstrapSaveRecipeModule() {
    if (!hasSaveRecipeTargets()) {
        if (bootstrapState.attempts < bootstrapState.maxAttempts - 1) {
            bootstrapState.attempts += 1;
            setTimeout(bootstrapSaveRecipeModule, 400);
        }
        return;
    }

    initializeSaveButtons();
    initializeCardStates();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapSaveRecipeModule, { once: true });
} else {
    bootstrapSaveRecipeModule();
}

/**
 * يهيئ الحالة الأولية للبطاقات
 */
function initializeCardStates() {
    const cardContainers = document.querySelectorAll('.card-container');
    cardContainers.forEach(card => {
        const saveButton = card.querySelector('.save-recipe-btn:not(#save-recipe-page-btn)');
        if (saveButton) {
            // تحديد الحالة الأولية بناءً على data-saved أو الكلاسات الموجودة
            const isSaved = saveButton.dataset.saved === 'true' || 
                           saveButton.classList.contains('bg-green-500') ||
                           saveButton.classList.contains('bg-green-600') ||
                           (saveButton.classList.contains('bg-green-500/80') && 
                            saveButton.classList.contains('text-white'));
            updateSaveButtonState(saveButton, isSaved);
            saveButton.dataset.saved = isSaved.toString();
        }
    });
}

/**
 * تحديث عداد الحفظ فوراً في صفحة الوصفة
 * @param {boolean} isSaved - هل الوصفة محفوظة أم لا
 */
function updateRecipePageSaveCountImmediate(isSaved) {
    console.log('IMMEDIATE update save count, isSaved:', isSaved);
    
    // البحث عن عداد الحفظ باستخدام ID
    let saveCountElement = document.getElementById('recipe-save-count');
    
    if (saveCountElement) {
        // استخراج العدد الحالي من النص
        const currentText = saveCountElement.textContent;
        console.log('Current text:', currentText);
        
        const currentCount = parseInt(currentText.match(/(\d+)/)?.[1] || '0');
        console.log('Current count:', currentCount);
        
        // حساب العدد الجديد
        const newCount = isSaved ? currentCount + 1 : Math.max(0, currentCount - 1);
        console.log('New count:', newCount);
        
        // تحديث النص فوراً
        saveCountElement.textContent = `${newCount} شخص حفظوا هذه الوصفة`;
        
        console.log(`IMMEDIATE Updated save count: ${currentCount} -> ${newCount} (saved: ${isSaved})`);
        
        // إضافة تأثير بصري للتأكيد
        saveCountElement.style.color = '#6b2e30';
        saveCountElement.style.fontWeight = 'bold';
        
        setTimeout(() => {
            saveCountElement.style.color = '';
            saveCountElement.style.fontWeight = '';
        }, 300);
        
        return true;
    } else {
        console.warn('IMMEDIATE: Could not find save count element');
        return false;
    }
}

/**
 * تحديث عداد الحفظ في صفحة الوصفة
 * @param {boolean} isSaved - هل الوصفة محفوظة أم لا
 */
function updateRecipePageSaveCount(isSaved) {
    console.log('Attempting to update save count, isSaved:', isSaved);
    
    // البحث عن عداد الحفظ باستخدام ID
    let saveCountElement = document.getElementById('recipe-save-count');
    
    // إذا لم نجد العنصر بالID، نبحث بطريقة أخرى
    if (!saveCountElement) {
        // البحث عن span يحتوي على "شخص حفظوا هذه الوصفة"
        const allSpans = document.querySelectorAll('span');
        for (let span of allSpans) {
            if (span.textContent && span.textContent.includes('شخص حفظوا هذه الوصفة')) {
                saveCountElement = span;
                console.log('Found element via text search:', saveCountElement);
                break;
            }
        }
    }
    
    // إذا لم نجد العنصر بعد، نبحث في جميع العناصر
    if (!saveCountElement) {
        const allElements = document.querySelectorAll('*');
        for (let element of allElements) {
            if (element.textContent && element.textContent.includes('شخص حفظوا هذه الوصفة')) {
                saveCountElement = element;
                console.log('Found element via comprehensive search:', saveCountElement);
                break;
            }
        }
    }
    
    // إذا لم نجد العنصر، نتوقف (لا نعيد المحاولة لتجنب التكرار اللانهائي)
    if (!saveCountElement) {
        console.log('Element not found, skipping update to avoid infinite loop');
        return;
    }
    
    if (saveCountElement) {
        // استخراج العدد الحالي من النص
        const currentText = saveCountElement.textContent;
        console.log('Current text:', currentText);
        
        const currentCount = parseInt(currentText.match(/(\d+)/)?.[1] || '0');
        console.log('Current count:', currentCount);
        
        // حساب العدد الجديد
        const newCount = isSaved ? currentCount + 1 : Math.max(0, currentCount - 1);
        console.log('New count:', newCount);
        
        // تحديث النص
        saveCountElement.textContent = `${newCount} شخص حفظوا هذه الوصفة`;
        
        console.log(`Updated save count: ${currentCount} -> ${newCount} (saved: ${isSaved})`);
        
        // إضافة تأثير بصري للتأكيد
        saveCountElement.style.transform = 'scale(1.1)';
        saveCountElement.style.transition = 'transform 0.2s ease';
        setTimeout(() => {
            saveCountElement.style.transform = 'scale(1)';
        }, 200);
        
        // إضافة تأثير لوني للتأكيد
        saveCountElement.style.color = '#6b2e30';
        saveCountElement.style.fontWeight = 'bold';
        setTimeout(() => {
            saveCountElement.style.color = '';
            saveCountElement.style.fontWeight = '';
        }, 500);
        
    } else {
        console.warn('Could not find save count element to update');
        console.log('Available elements with ID recipe-save-count:', document.querySelectorAll('#recipe-save-count'));
    }
}

// دالة اختبار للتأكد من عمل التحديث
function testSaveCountUpdate() {
    console.log('Testing save count update...');
    const element = document.getElementById('recipe-save-count');
    if (element) {
        console.log('Found element:', element);
        console.log('Current text:', element.textContent);
        updateRecipePageSaveCount(true);
    } else {
        console.log('Element not found');
    }
}

// تصدير الدوال للاستخدام في ملفات أخرى
window.SaveRecipe = {
    showToast,
    updateSaveButtonState,
    handleSaveRecipe,
    initializeSaveButtons,
    initializeSaveButtonWithState,
    updateRecipePageSaveCount,
    testSaveCountUpdate
};



