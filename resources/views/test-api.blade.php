@extends('layouts.app')

@section('title', 'Test API Response')

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
        background: #eaf3f6;
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
    <h1 class="text-3xl font-bold mb-6">Test Recipe API Response</h1>
    
    <div class="test-section">
        <h2 class="text-xl font-semibold mb-4">API Test Results</h2>
        <div id="api-test-results">
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-2">Testing API...</p>
            </div>
        </div>
    </div>
    
    <div class="test-section">
        <h2 class="text-xl font-semibold mb-4">Test Actions</h2>
        <button onclick="runApiTest()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
            Run API Test
        </button>
        <button onclick="clearResults()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Clear Results
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function runApiTest() {
        const resultsDiv = document.getElementById('api-test-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Testing API...</p></div>';
        
        try {
            // Test the API endpoint
            const response = await fetch('/api/recipes/1', {
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
                    <p><strong>Response Time:</strong> ${new Date().toLocaleTimeString()}</p>
                </div>
                
                <div class="test-section">
                    <h3 class="font-semibold mb-2">Response Data:</h3>
                    <div class="code-block">${JSON.stringify(data, null, 2)}</div>
                </div>
            `;
            
            if (data.tools && Array.isArray(data.tools)) {
                html += `
                    <div class="test-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ Tools Found</h3>
                        <p><strong>Number of tools:</strong> ${data.tools.length}</p>
                        <ul class="list-disc list-inside mt-2">
                            ${data.tools.map(tool => `<li>${tool}</li>`).join('')}
                        </ul>
                    </div>
                `;
            } else {
                html += `
                    <div class="test-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Tools Found</h3>
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
    
    function clearResults() {
        document.getElementById('api-test-results').innerHTML = '<p class="text-gray-500">Results cleared. Click "Run API Test" to test again.</p>';
    }
    
    // Auto-run test on page load
    document.addEventListener('DOMContentLoaded', function() {
        runApiTest();
    });
</script>
@endpush

