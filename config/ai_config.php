<?php
// Google Gemini AI Configuration
// Get your free API key from: https://makersuite.google.com/app/apikey

define('GEMINI_API_KEY', 'AIzaSyB616iTBjBeRz_aOFCnlmEZ2Isk-cniSL4'); 
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// AI Helper Functions
class RealAI {
    private $apiKey;
    private $apiUrl;
    
    public function __construct() {
        $this->apiKey = GEMINI_API_KEY;
        $this->apiUrl = GEMINI_API_URL;
    }
    
    /**
     * Send request to Gemini AI
     */
    public function analyze($prompt) {
        $url = $this->apiUrl . '?key=' . $this->apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }
        }
        
        return null;
    }
    
    /**
     * Generate AI insights from habit data
     */
    public function generateInsights($userData) {
        $prompt = $this->buildPrompt($userData);
        $response = $this->analyze($prompt);
        
        if ($response) {
            return $this->parseInsights($response);
        }
        
        return null;
    }
    
    /**
     * Build comprehensive prompt for AI
     */
    private function buildPrompt($data) {
        $prompt = "You are an expert habit tracking coach and data analyst. Analyze this user's habit data and provide personalized insights.\n\n";
        
        $prompt .= "USER HABIT DATA:\n";
        $prompt .= "- Total Habits: {$data['total_habits']}\n";
        $prompt .= "- Completion Rate (30 days): {$data['completion_rate']}%\n";
        $prompt .= "- Current Streak: {$data['streak']} days\n";
        $prompt .= "- Total Completions: {$data['total_completions']}\n";
        $prompt .= "- Average Daily Completions: {$data['avg_daily']}\n";
        
        if (!empty($data['weak_days'])) {
            $prompt .= "- Weak Days: " . implode(', ', $data['weak_days']) . "\n";
        }
        
        if (!empty($data['best_time'])) {
            $prompt .= "- Best Performance Time: {$data['best_time']}\n";
        }
        
        if (!empty($data['struggling_habits'])) {
            $prompt .= "- Struggling Habits:\n";
            foreach ($data['struggling_habits'] as $habit) {
                $prompt .= "  * {$habit['title']} - {$habit['completions']} completions out of {$data['days_active']} days\n";
            }
        }
        
        if (!empty($data['top_habits'])) {
            $prompt .= "- Top Performing Habits:\n";
            foreach ($data['top_habits'] as $habit) {
                $prompt .= "  * {$habit['title']} - {$habit['completions']} completions\n";
            }
        }
        
        $prompt .= "\nBased on this data, provide:\n";
        $prompt .= "1. PERFORMANCE_ANALYSIS: A detailed 2-3 sentence analysis of overall performance\n";
        $prompt .= "2. KEY_STRENGTHS: 2-3 specific strengths (bullet points)\n";
        $prompt .= "3. AREAS_FOR_IMPROVEMENT: 2-3 specific areas needing work (bullet points)\n";
        $prompt .= "4. ACTIONABLE_RECOMMENDATIONS: 3-4 specific, practical recommendations (numbered list)\n";
        $prompt .= "5. MOTIVATIONAL_MESSAGE: An encouraging 1-2 sentence message\n";
        $prompt .= "6. NEXT_MILESTONE: Suggest a challenging but achievable goal\n\n";
        $prompt .= "Format your response exactly like this:\n";
        $prompt .= "PERFORMANCE_ANALYSIS: [your analysis]\n";
        $prompt .= "KEY_STRENGTHS:\n- [strength 1]\n- [strength 2]\n";
        $prompt .= "AREAS_FOR_IMPROVEMENT:\n- [area 1]\n- [area 2]\n";
        $prompt .= "ACTIONABLE_RECOMMENDATIONS:\n1. [recommendation 1]\n2. [recommendation 2]\n3. [recommendation 3]\n";
        $prompt .= "MOTIVATIONAL_MESSAGE: [your message]\n";
        $prompt .= "NEXT_MILESTONE: [milestone suggestion]\n";
        
        return $prompt;
    }
    
    /**
     * Parse AI response into structured data
     */
    private function parseInsights($response) {
        $insights = [];
        
        // Extract sections using regex
        if (preg_match('/PERFORMANCE_ANALYSIS:\s*(.+?)(?=KEY_STRENGTHS:|$)/s', $response, $matches)) {
            $insights['analysis'] = trim($matches[1]);
        }
        
        if (preg_match('/KEY_STRENGTHS:\s*(.+?)(?=AREAS_FOR_IMPROVEMENT:|$)/s', $response, $matches)) {
            $insights['strengths'] = $this->extractBulletPoints($matches[1]);
        }
        
        if (preg_match('/AREAS_FOR_IMPROVEMENT:\s*(.+?)(?=ACTIONABLE_RECOMMENDATIONS:|$)/s', $response, $matches)) {
            $insights['improvements'] = $this->extractBulletPoints($matches[1]);
        }
        
        if (preg_match('/ACTIONABLE_RECOMMENDATIONS:\s*(.+?)(?=MOTIVATIONAL_MESSAGE:|$)/s', $response, $matches)) {
            $insights['recommendations'] = $this->extractNumberedPoints($matches[1]);
        }
        
        if (preg_match('/MOTIVATIONAL_MESSAGE:\s*(.+?)(?=NEXT_MILESTONE:|$)/s', $response, $matches)) {
            $insights['motivation'] = trim($matches[1]);
        }
        
        if (preg_match('/NEXT_MILESTONE:\s*(.+?)$/s', $response, $matches)) {
            $insights['milestone'] = trim($matches[1]);
        }
        
        return $insights;
    }
    
    /**
     * Extract bullet points from text
     */
    private function extractBulletPoints($text) {
        $lines = explode("\n", $text);
        $points = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^[-•*]\s*(.+)$/', $line, $matches)) {
                $points[] = trim($matches[1]);
            }
        }
        
        return $points;
    }
    
    /**
     * Extract numbered points from text
     */
    private function extractNumberedPoints($text) {
        $lines = explode("\n", $text);
        $points = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d+\.\s*(.+)$/', $line, $matches)) {
                $points[] = trim($matches[1]);
            }
        }
        
        return $points;
    }
}
?>