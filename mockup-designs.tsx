import React from 'react';

const MockupUI = () => {
  return (
    <div className="font-sans text-gray-800">
      <h1 className="text-2xl font-bold mb-6">Review Showcase Plugin UI Mockups</h1>
      
      {/* Dashboard View */}
      <div className="mb-12 border rounded-lg overflow-hidden shadow-lg">
        <div className="bg-gray-800 text-white p-4">
          <h2 className="font-bold">Dashboard View</h2>
        </div>
        <div className="p-4 bg-gray-100">
          <div className="flex">
            {/* Sidebar */}
            <div className="w-1/5 bg-white p-4 rounded-lg shadow mr-4">
              <div className="font-bold mb-4">Review Showcase</div>
              <ul>
                <li className="py-2 px-4 bg-blue-100 rounded font-medium">Dashboard</li>
                <li className="py-2 px-4">My Reviews</li>
                <li className="py-2 px-4">Video Templates</li>
                <li className="py-2 px-4">My Videos</li>
                <li className="py-2 px-4">Settings</li>
              </ul>
            </div>
            
            {/* Main Content */}
            <div className="w-4/5 bg-white rounded-lg shadow p-6">
              <div className="flex justify-between mb-6">
                <h3 className="text-xl font-bold">Recent Reviews</h3>
                <button className="bg-blue-500 text-white px-4 py-2 rounded">Sync Reviews</button>
              </div>
              
              <div className="flex gap-4 mb-6">
                <div className="p-3 bg-blue-50 rounded-lg shadow flex-1 flex items-center">
                  <div className="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white text-2xl mr-4">
                    <span>24</span>
                  </div>
                  <div>
                    <div className="text-sm text-gray-500">Total Reviews</div>
                    <div className="font-bold">24 Reviews</div>
                  </div>
                </div>
                
                <div className="p-3 bg-green-50 rounded-lg shadow flex-1 flex items-center">
                  <div className="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl mr-4">
                    <span>4.8</span>
                  </div>
                  <div>
                    <div className="text-sm text-gray-500">Average Rating</div>
                    <div className="font-bold">4.8 Stars</div>
                  </div>
                </div>
                
                <div className="p-3 bg-purple-50 rounded-lg shadow flex-1 flex items-center">
                  <div className="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white text-2xl mr-4">
                    <span>5</span>
                  </div>
                  <div>
                    <div className="text-sm text-gray-500">Videos Created</div>
                    <div className="font-bold">5 Videos</div>
                  </div>
                </div>
              </div>
              
              <div className="mb-6">
                <h4 className="font-bold mb-2">Top Reviews</h4>
                <div className="grid grid-cols-2 gap-4">
                  {[1, 2, 3, 4].map((item) => (
                    <div key={item} className="border rounded-lg p-4 hover:shadow-md">
                      <div className="flex mb-2">
                        <div className="flex text-yellow-400 mr-2">
                          {'★★★★★'.split('').map((star, i) => (
                            <span key={i}>{star}</span>
                          ))}
                        </div>
                        <div className="text-sm text-gray-500">2 days ago</div>
                      </div>
                      <p className="text-sm mb-3">
                        "Absolutely amazing service! The staff went above and beyond to help me. Would definitely recommend to anyone looking for quality service."
                      </p>
                      <div className="flex justify-between items-center">
                        <div className="text-sm font-medium">- John Smith</div>
                        <button className="bg-green-500 text-white px-2 py-1 rounded text-sm">Create Video</button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {/* Video Editor View */}
      <div className="mb-12 border rounded-lg overflow-hidden shadow-lg">
        <div className="bg-gray-800 text-white p-4">
          <h2 className="font-bold">Video Editor View</h2>
        </div>
        <div className="p-4 bg-gray-100">
          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex justify-between mb-6">
              <h3 className="text-xl font-bold">Create Video from Review</h3>
              <button className="bg-gray-200 text-gray-800 px-4 py-2 rounded">Cancel</button>
            </div>
            
            <div className="flex gap-6">
              {/* Left Panel - Controls */}
              <div className="w-1/3 border rounded-lg p-4">
                <div className="mb-6">
                  <h4 className="font-bold mb-3">Review Content</h4>
                  <div className="border rounded-lg p-3 bg-gray-50 mb-2">
                    <div className="flex text-yellow-400 mb-2">
                      {'★★★★★'.split('').map((star, i) => (
                        <span key={i}>{star}</span>
                      ))}
                    </div>
                    <p className="text-sm">
                      "Absolutely amazing service! The staff went above and beyond to help me. Would definitely recommend to anyone looking for quality service."
                    </p>
                    <div className="text-sm font-medium mt-2">- John Smith</div>
                  </div>
                  <button className="text-blue-500 text-sm">Edit Text</button>
                </div>
                
                <div className="mb-6">
                  <h4 className="font-bold mb-3">Background Video</h4>
                  <div className="grid grid-cols-3 gap-2 mb-3">
                    {[1, 2, 3, 4, 5, 6].map((item) => (
                      <div key={item} className={`h-16 bg-gray-300 rounded ${item === 1 ? 'ring-2 ring-blue-500' : ''}`}></div>
                    ))}
                  </div>
                  <button className="text-blue-500 text-sm">Browse More</button>
                </div>
                
                <div className="mb-6">
                  <h4 className="font-bold mb-3">Text Style</h4>
                  <div className="flex flex-wrap gap-2 mb-3">
                    <button className="px-3 py-1 border rounded bg-white">Aa</button>
                    <button className="px-3 py-1 border rounded bg-white">Aa</button>
                    <button className="px-3 py-1 border rounded bg-blue-500 text-white">Aa</button>
                    <button className="px-3 py-1 border rounded bg-white">Aa</button>
                  </div>
                  
                  <div className="flex gap-2 mb-3">
                    <button className="flex-1 py-1 border rounded bg-white">White</button>
                    <button className="flex-1 py-1 border rounded bg-blue-500 text-white">Blue</button>
                    <button className="flex-1 py-1 border rounded bg-white">Custom</button>
                  </div>
                </div>
                
                <div className="mb-6">
                  <h4 className="font-bold mb-3">Video Format</h4>
                  <div className="flex gap-2">
                    <button className="flex-1 py-1 border rounded bg-blue-500 text-white">16:9</button>
                    <button className="flex-1 py-1 border rounded bg-white">1:1</button>
                    <button className="flex-1 py-1 border rounded bg-white">9:16</button>
                  </div>
                </div>
                
                <button className="w-full bg-green-500 text-white py-2 rounded font-medium">Generate Video</button>
              </div>
              
              {/* Right Panel - Preview */}
              <div className="w-2/3">
                <div className="bg-black rounded-lg aspect-video flex items-center justify-center relative">
                  <div className="absolute inset-0 opacity-50 flex items-center justify-center">
                    <img src="/api/placeholder/640/360" alt="Sample background" className="w-full h-full object-cover" />
                  </div>
                  <div className="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <div className="mb-2 flex">
                      {'★★★★★'.split('').map((star, i) => (
                        <span key={i} className="text-yellow-400">{star}</span>
                      ))}
                    </div>
                    <p className="text-lg font-medium mb-2">
                      "Absolutely amazing service! The staff went above and beyond to help me."
                    </p>
                    <div className="font-medium">- John Smith</div>
                  </div>
                </div>
                <div className="mt-4 text-center text-sm text-gray-500">
                  Preview - Drag text to reposition
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {/* Processing View */}
      <div className="mb-12 border rounded-lg overflow-hidden shadow-lg">
        <div className="bg-gray-800 text-white p-4">
          <h2 className="font-bold">Video Processing View</h2>
        </div>
        <div className="p-4 bg-gray-100">
          <div className="bg-white rounded-lg shadow p-6 text-center">
            <h3 className="text-xl font-bold mb-6">Processing Your Video</h3>
            
            <div className="max-w-md mx-auto mb-8">
              <div className="h-2 bg-gray-200 rounded-full mb-2">
                <div className="h-2 bg-blue-500 rounded-full w-2/3"></div>
              </div>
              <div className="flex justify-between text-sm text-gray-500">
                <span>Encoding video...</span>
                <span>67%</span>
              </div>
            </div>
            
            <p className="text-gray-600 mb-4">This may take a few minutes. Please don't close this window.</p>
            <p className="text-sm text-gray-500">Estimated time remaining: 1:24</p>
          </div>
        </div>
      </div>
      
      {/* Completed Video View */}
      <div className="border rounded-lg overflow-hidden shadow-lg">
        <div className="bg-gray-800 text-white p-4">
          <h2 className="font-bold">Completed Video View</h2>
        </div>
        <div className="p-4 bg-gray-100">
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-xl font-bold mb-6 text-center">Your Video is Ready!</h3>
            
            <div className="flex justify-center mb-6">
              <div className="bg-black rounded-lg w-2/3 aspect-video flex items-center justify-center">
                <img src="/api/placeholder/640/360" alt="Final video thumbnail" className="w-full h-full object-cover" />
              </div>
            </div>
            
            <div className="flex justify-center gap-4 mb-8">
              <button className="bg-blue-500 text-white px-6 py-2 rounded flex items-center">
                <span>Download Video</span>
              </button>
              <button className="bg-green-500 text-white px-6 py-2 rounded flex items-center">
                <span>Create Another</span>
              </button>
            </div>
            
            <div className="border-t pt-6">
              <h4 className="font-bold mb-3 text-center">Coming Soon: Direct Social Sharing</h4>
              <div className="flex justify-center gap-4">
                <div className="p-2 bg-gray-200 rounded-full w-10 h-10"></div>
                <div className="p-2 bg-gray-200 rounded-full w-10 h-10"></div>
                <div className="p-2 bg-gray-200 rounded-full w-10 h-10"></div>
                <div className="p-2 bg-gray-200 rounded-full w-10 h-10"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MockupUI;
