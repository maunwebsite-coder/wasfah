@extends('layouts.app')

@section('title', 'Test Recipe Page')

@push('styles')
<style>
    .test-section { 
        margin: 20px 0; 
        padding: 20px; 
        border: 1px solid #ccc; 
        border-radius: 8px;
        background: #f9f9f9;
    }
    .test-result {
        background: #e8f5e8;
        border: 1px solid #4caf50;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .test-error {
        background: #f4f6ff;
        border: 1px solid #f44336;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .test-warning {
        background: #fff3e0;
        border: 1px solid #1ab8ff;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .code-block {
        background: #f5f5f5;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        font-family: monospace;
        white-space: pre-wrap;
        overflow-x: auto;
        max-height: 300px;
    }
    .recipe-info {
        background: #e3f2fd;
        border: 1px solid #2196f3;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Test Recipe Page</h1>
    
    <div class="test-section">
        <h2 class="text-xl font-semibold mb-4">Recipe Information</h2>
        <div id="recipe-info">
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-2">Loading recipe data...</p>
            </div>
        </div>
    </div>
    
    <div class="test-section">
        <h2 class="text-xl font-semibold mb-4">API Test Results</h2>
        <div id="api-test-results">
            <p class="text-gray-500">Click "Run Tests" to test the API</p>
        </div>
    </div>
    
    <div class="test-section">
        <h2 class="text-xl font-semibold mb-4">Page Test Results</h2>
        <div id="page-test-results">
            <p class="text-gray-500">Click "Run Tests" to test the page</p>
        </div>
    </div>
    
    <div class="test-section">
        <h2 class="text-xl font-semibold mb-4">Test Actions</h2>
        <button onclick="runAllTests()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
            Run All Tests
        </button>
        <button onclick="runApiTest()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
            Test API Only
        </button>
        <button onclick="runPageTest()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mr-2">
            Test Page Only
        </button>
        <button onclick="clearResults()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Clear Results
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentRecipe = null;
    
    async function loadRecipeInfo() {
        try {
            const response = await fetch('/api/recipes', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data && data.length > 0) {
                currentRecipe = data[0];
                document.getElementById('recipe-info').innerHTML = `
                    <div class="recipe-info">
                        <h3 class="font-semibold text-blue-700 mb-2">Recipe Found</h3>
                        <p><strong>ID:</strong> ${currentRecipe.recipe_id || currentRecipe.id}</p>
                        <p><strong>Title:</strong> ${currentRecipe.title}</p>
                        <p><strong>Tools Count:</strong> ${currentRecipe.tools ? currentRecipe.tools.length : 0}</p>
                        ${currentRecipe.tools ? `<p><strong>Tools:</strong> ${currentRecipe.tools.join(', ')}</p>` : ''}
                    </div>
                `;
            } else {
                document.getElementById('recipe-info').innerHTML = `
                    <div class="test-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Recipes Found</h3>
                        <p>No recipes found in the database</p>
                    </div>
                `;
            }
        } catch (error) {
            document.getElementById('recipe-info').innerHTML = `
                <div class="test-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ Error Loading Recipe</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                </div>
            `;
        }
    }
    
    async function runApiTest() {
        if (!currentRecipe) {
            document.getElementById('api-test-results').innerHTML = `
                <div class="test-warning">
                    <h3 class="font-semibold text-orange-700 mb-2">⚠ No Recipe Available</h3>
                    <p>Please load recipe information first</p>
                </div>
            `;
            return;
        }
        
        const resultsDiv = document.getElementById('api-test-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Testing API...</p></div>';
        
        try {
            const recipeId = currentRecipe.recipe_id || currentRecipe.id;
            const response = await fetch(`/api/recipes/${recipeId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            const data = await response.json();
            
            let html = `
                <div class="test-result">
                    <h3 class="font-semibold text-green-700 mb-2">✓ API Test Successful</h3>
                    <p><strong>Status Code:</strong> ${response.status}</p>
                    <p><strong>Recipe ID:</strong> ${recipeId}</p>
                    <p><strong>Response Time:</strong> ${new Date().toLocaleTimeString()}</p>
                </div>
                
                <div class="test-section">
                    <h3 class="font-semibold mb-2">API Response Data:</h3>
                    <div class="code-block">${JSON.stringify(data, null, 2)}</div>
                </div>
            `;
            
            if (data.tools && Array.isArray(data.tools)) {
                html += `
                    <div class="test-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ Tools Found in API</h3>
                        <p><strong>Number of tools:</strong> ${data.tools.length}</p>
                        <ul class="list-disc list-inside mt-2">
                            ${data.tools.map(tool => `<li>${tool}</li>`).join('')}
                        </ul>
                    </div>
                `;
            } else {
                html += `
                    <div class="test-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Tools Found in API</h3>
                        <p>No tools array found in the API response</p>
                    </div>
                `;
            }
            
            resultsDiv.innerHTML = html;
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="test-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ API Test Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <div class="code-block">${error.stack}</div>
                </div>
            `;
        }
    }
    
    async function runPageTest() {
        if (!currentRecipe) {
            document.getElementById('page-test-results').innerHTML = `
                <div class="test-warning">
                    <h3 class="font-semibold text-orange-700 mb-2">⚠ No Recipe Available</h3>
                    <p>Please load recipe information first</p>
                </div>
            `;
            return;
        }
        
        const resultsDiv = document.getElementById('page-test-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Testing page...</p></div>';
        
        try {
            const recipeId = currentRecipe.recipe_id || currentRecipe.id;
            const url = `${window.location.origin}/recipe/${recipeId}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                },
                credentials: 'include'
            });
            
            const html = await response.text();
            
            let htmlResults = `
                <div class="test-result">
                    <h3 class="font-semibold text-green-700 mb-2">✓ Page Test Successful</h3>
                    <p><strong>Status Code:</strong> ${response.status}</p>
                    <p><strong>URL:</strong> ${url}</p>
                    <p><strong>Response Time:</strong> ${new Date().toLocaleTimeString()}</p>
                </div>
            `;
            
            // Check for tools-container
            if (html.includes('id="tools-container"')) {
                htmlResults += `
                    <div class="test-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ tools-container Found</h3>
                        <p>tools-container element found in HTML</p>
                    </div>
                `;
            } else {
                htmlResults += `
                    <div class="test-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ tools-container NOT Found</h3>
                        <p>tools-container element not found in HTML</p>
                    </div>
                `;
            }
            
            // Check for recipe.js
            if (html.includes('recipe.js') || html.includes('recipe-')) {
                htmlResults += `
                    <div class="test-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ Recipe.js Found</h3>
                        <p>recipe.js script found in HTML</p>
                    </div>
                `;
            } else {
                htmlResults += `
                    <div class="test-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ Recipe.js NOT Found</h3>
                        <p>recipe.js script not found in HTML</p>
                    </div>
                `;
            }
            
            resultsDiv.innerHTML = htmlResults;
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="test-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ Page Test Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <div class="code-block">${error.stack}</div>
                </div>
            `;
        }
    }
    
    async function runAllTests() {
        await loadRecipeInfo();
        await runApiTest();
        await runPageTest();
    }
    
    function clearResults() {
        document.getElementById('recipe-info').innerHTML = '<p class="text-gray-500">Click "Run All Tests" to load recipe information.</p>';
        document.getElementById('api-test-results').innerHTML = '<p class="text-gray-500">Click "Run All Tests" to test the API.</p>';
        document.getElementById('page-test-results').innerHTML = '<p class="text-gray-500">Click "Run All Tests" to test the page.</p>';
    }
    
    // Auto-run tests on page load
    document.addEventListener('DOMContentLoaded', function() {
        runAllTests();
    });
</script>
@endpush





