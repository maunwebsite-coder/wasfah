@extends('layouts.app')

@section('title', 'Check DOM Elements')

@push('styles')
<style>
    .check-section { 
        margin: 20px 0; 
        padding: 20px; 
        border: 1px solid #ccc; 
        border-radius: 8px;
        background: #f9f9f9;
    }
    .check-result {
        background: #e8f5e8;
        border: 1px solid #4caf50;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .check-error {
        background: #f4f6ff;
        border: 1px solid #f44336;
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
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Check DOM Elements in Recipe Page</h1>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">DOM Elements Check Results</h2>
        <div id="dom-check-results">
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-2">Checking DOM elements...</p>
            </div>
        </div>
    </div>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">Check Actions</h2>
        <button onclick="runDomCheck()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
            Run DOM Check
        </button>
        <button onclick="clearResults()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Clear Results
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function runDomCheck() {
        const resultsDiv = document.getElementById('dom-check-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Checking DOM elements...</p></div>';
        
        try {
            // Get the first recipe ID
            const recipesResponse = await fetch('/api/recipes', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            const recipes = await recipesResponse.json();
            
            if (!recipes || recipes.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Recipes Found</h3>
                        <p>No recipes found to check</p>
                    </div>
                `;
                return;
            }
            
            const recipeId = recipes[0].recipe_id || recipes[0].id;
            const url = `${window.location.origin}/recipe/${recipeId}`;
            
            // Fetch the recipe page
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                },
                credentials: 'include'
            });
            
            const html = await response.text();
            
            let htmlResults = `
                <div class="check-result">
                    <h3 class="font-semibold text-green-700 mb-2">✓ Page Loaded Successfully</h3>
                    <p><strong>URL:</strong> ${url}</p>
                    <p><strong>Status Code:</strong> ${response.status}</p>
                    <p><strong>Check Time:</strong> ${new Date().toLocaleTimeString()}</p>
                </div>
            `;
            
            // Check for specific DOM elements
            const elementsToCheck = [
                { id: 'steps-list', name: 'Steps List' },
                { id: 'tools-container', name: 'Tools Container' },
                { id: 'recipe-title', name: 'Recipe Title' },
                { id: 'ingredients-list', name: 'Ingredients List' }
            ];
            
            elementsToCheck.forEach(element => {
                if (html.includes(`id="${element.id}"`)) {
                    htmlResults += `
                        <div class="check-result">
                            <h3 class="font-semibold text-green-700 mb-2">✓ ${element.name} Found</h3>
                            <p>Element with ID "${element.id}" found in HTML</p>
                        </div>
                    `;
                } else {
                    htmlResults += `
                        <div class="check-error">
                            <h3 class="font-semibold text-red-700 mb-2">✗ ${element.name} NOT Found</h3>
                            <p>Element with ID "${element.id}" not found in HTML</p>
                        </div>
                    `;
                }
            });
            
            // Check for recipe.js file
            if (html.includes('recipe-C2GwO11r.js')) {
                htmlResults += `
                    <div class="check-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ Recipe.js File Loaded</h3>
                        <p>recipe.js file is loaded in the page</p>
                    </div>
                `;
            } else {
                htmlResults += `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ Recipe.js File NOT Loaded</h3>
                        <p>recipe.js file is not loaded in the page</p>
                    </div>
                `;
            }
            
            // Check for JavaScript error handling
            if (html.includes('console.error')) {
                htmlResults += `
                    <div class="check-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ JavaScript Error Handling Found</h3>
                        <p>JavaScript error handling is present</p>
                    </div>
                `;
            } else {
                htmlResults += `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No JavaScript Error Handling Found</h3>
                        <p>No JavaScript error handling found</p>
                    </div>
                `;
            }
            
            // Check for Vite assets
            if (html.includes('vite')) {
                htmlResults += `
                    <div class="check-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ Vite Assets Found</h3>
                        <p>Vite assets are being loaded</p>
                    </div>
                `;
            } else {
                htmlResults += `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ Vite Assets NOT Found</h3>
                        <p>Vite assets are not being loaded</p>
                    </div>
                `;
            }
            
            // Extract and show tools-container content if found
            const toolsContainerMatch = html.match(/<div id="tools-container"[^>]*>(.*?)<\/div>/s);
            if (toolsContainerMatch) {
                htmlResults += `
                    <div class="check-section">
                        <h3 class="font-semibold mb-2">Tools Container Content:</h3>
                        <div class="code-block">${toolsContainerMatch[1]}</div>
                    </div>
                `;
            }
            
            resultsDiv.innerHTML = htmlResults;
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="check-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ DOM Check Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <div class="code-block">${error.stack}</div>
                </div>
            `;
        }
    }
    
    function clearResults() {
        document.getElementById('dom-check-results').innerHTML = '<p class="text-gray-500">Click "Run DOM Check" to check DOM elements.</p>';
    }
    
    // Auto-run check on page load
    document.addEventListener('DOMContentLoaded', function() {
        runDomCheck();
    });
</script>
@endpush


