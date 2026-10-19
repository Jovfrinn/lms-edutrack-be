<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuizResult;
use App\Models\Quizzes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ? $request->search : "";
        $quiz = Quizzes::when($search, function ($query, $search) {
            $query->where('quiz_title', 'LIKE', '%' . $search . '%');
        })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $quiz,
            'success' => true,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*.questionText' => 'required|string',
            'questions.*.points' => 'required|integer|min:0',
            'questions.*.choices' => 'required|array|min:2',
            'questions.*.choices.*.text' => 'required|string',

        ]);

        DB::beginTransaction();

        try {
            $userId = Auth::id() ?? 1;

            $quiz = Quizzes::create([
                'user_id' => $userId,
                'quiz_title' => $validated['title'],
                'description' => $validated['description'],
                'total_questions' => count($validated['questions']),
            ]);

            $totalPoints = 0;

            foreach ($request->questions as $questionData) {
                $totalPoints += (int) $questionData['points'];
                $questionImage = null;

                if (isset($questionData['questionImage']) && $questionData['questionImage'] instanceof \Illuminate\Http\UploadedFile) {
                    $questionImage = $questionData['questionImage']->store('quiz_media/images', 'public');
                }

                $question = $quiz->questions()->create([
                    'question_text' => $questionData['questionText'],
                    'media_path' => $questionImage,
                    'point' => $questionData['points'],
                ]);

                foreach ($questionData['choices'] as $choiceData) {
                    $answerImage = null;

                    if (isset($choiceData['img']) && $choiceData['img'] instanceof \Illuminate\Http\UploadedFile) {
                        $answerImage = $choiceData['img']->store('quiz_media/answers', 'public');
                    }

                    $question->answers()->create([
                        'quiz_question_id' => $question->id,
                        'answer_text' => $choiceData['text'],
                        'media_path' => $answerImage,
                        'is_correct' => $choiceData['correct'] ?? false,
                    ]);
                }
            }

            $quiz->update([
                'total_point' => $totalPoints,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Quiz berhasil dibuat!',
                'quiz_id' => $quiz->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan kuis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeResult(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id', // Pastikan ID kuis valid
            'total_point' => 'required|integer|min:0',
            // user_id harus selalu ada, bisa diambil dari Auth::id() atau request
        ]);

        $userId = Auth::id(); // Ambil ID pengguna yang sedang login

        if (is_null($userId)) {
            // Jika user tidak login, kembalikan response 401 Unauthorized
            return response()->json([
                'message' => 'Anda harus login untuk menyelesaikan kuis.'
            ], 401);
        }
        // 2. Cek Ketersediaan (PENCEGAHAN ULANGI)
        // Jika Anda ingin mencegah pengguna submit berkali-kali:
        $existingResult = QuizResult::where('user_id', $userId)
            ->where('quiz_id', $request->quiz_id)
            ->first();

        if ($existingResult) {
            // Jika hasil sudah ada, tolak request atau update nilai (tergantung kebutuhan)
            return response()->json([
                'message' => 'Anda sudah menyelesaikan kuis ini.',
                'result' => $existingResult
            ], 403); // Forbidden
        }

        // 3. Simpan Hasil ke Database
        try {
            $result = QuizResult::create([
                'user_id' => $userId,
                'quiz_id' => $request->quiz_id,
                'total_point' => $request->total_point,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hasil kuis berhasil disimpan!',
                'data' => $result
            ], 201); // Created
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $quiz = Quizzes::with(['questions' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }, 'questions.answers'])
                ->findOrFail($id);

            return response()->json([
                'message' => 'Quiz data retrieved successfully',
                'data' => $quiz
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Quiz not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showDetail(string $id)
    {
        $userId = Auth::id(); // Ambil ID pengguna saat ini
        try {
            // Asumsi model kuis Anda bernama Quiz
            $quiz = Quizzes::where('id', $id)
                ->with([
                    // Relasi ke pertanyaan
                    'questions' => function ($q) {
                        $q->select('id', 'quiz_id', 'question_text', 'point')
                            ->with([
                                'answers' => function ($subQ) {
                                    $subQ->select('id', 'quiz_question_id', 'answer_text', 'media_path', 'is_correct');
                                }
                            ]);
                    }
                ])
                ->firstOrFail();


            // Cek hasil kuis yang sudah ada untuk pengguna ini
            $existingResult = QuizResult::where('quiz_id', $quiz->id)
                ->where('user_id', $userId)
                ->first();

            // Tambahkan status ke response data
            $quiz->has_finished = (bool) $existingResult;
            $quiz->user_score = $existingResult ? $existingResult->total_point : null;

            return response()->json([
                'success' => true,
                'data' => $quiz
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Kuis tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function leaderboard($id)
    {
        $userId = Auth::id();

        $topTen = QuizResult::select('user_id', DB::raw('SUM(total_point) as total_score'))
            ->where('quiz_id', $id)
            ->with('user.employee')
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->limit(10)
            ->get();

        $currentUserData = null;
        $isUserInTopTen = $topTen->contains('user_id', $userId);

        if (!$isUserInTopTen) {
            $myScore = QuizResult::where('quiz_id', $id)
                ->where('user_id', $userId)
                ->sum('total_point');

            if ($myScore > 0 || QuizResult::where('quiz_id', $id)->where('user_id', $userId)->exists()) {

                $rankCount = DB::table('quiz_results')
                    ->select('user_id', DB::raw('SUM(total_point) as total_score'))
                    ->where('quiz_id', $id)
                    ->groupBy('user_id')
                    ->having('total_score', '>', $myScore)
                    ->get()
                    ->count();

                $myRank = $rankCount + 1;

                $currentUserData = [
                    'rank' => $myRank,
                    'user' => Auth::user(),
                    'total_score' => $myScore
                ];
            }
        }

        // dd($topTen);

        return response()->json([
            'status' => 'success',
            'leaderboard' => $topTen,
            'currentUser' => $currentUserData
        ]);
    }
    public function leaderboardAll(Request $request)
    {
        $userId = Auth::id();

        $month = request('month');

        $topTen = QuizResult::select('user_id', DB::raw('SUM(total_point) as total_score'))
            ->with('user.employee')
            ->whereYear('created_at', Carbon::now()->year)
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('created_at', $month);
            })
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->get();


        $currentUserData = null;
        $isUserInTopTen = $topTen->contains('user_id', $userId);

        if (!$isUserInTopTen) {
            $myScore = QuizResult::where('user_id', $userId)
                ->sum('total_point');

            if ($myScore > 0 || QuizResult::where('user_id', $userId)->exists()) {

                $rankCount = DB::table('quiz_results')
                    ->select('user_id', DB::raw('SUM(total_point) as total_score'))
                    ->groupBy('user_id')
                    ->having('total_score', '>', $myScore)
                    ->get()
                    ->count();

                $myRank = $rankCount + 1;

                $currentUserData = [
                    'rank' => $myRank,
                    'user' => Auth::user(),
                    'total_score' => $myScore
                ];
            }
        }

        // dd($topTen);

        return response()->json([
            'status' => 'success',
            'leaderboard' => $topTen,
            'currentUser' => $currentUserData
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*.questionText' => 'required|string',
            'questions.*.points' => 'required|integer|min:0',
            'questions.*.choices' => 'required|array|min:2',
            'questions.*.choices.*.text' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $quiz = Quizzes::findOrFail($id);

            $quiz->update([
                'quiz_title' => $validated['title'],
                'description' => $validated['description'],
                'total_questions' => count($validated['questions']),
            ]);

            $existingQIds = collect($request->questions)
                ->pluck('id')
                ->filter(fn($val) => is_numeric($val))
                ->toArray();

            $quiz->questions()->whereNotIn('id', $existingQIds)->delete();

            $totalPoints = 0;

            foreach ($request->questions as $questionData) {
                $totalPoints += (int) $questionData['points'];

                $isExistingQuestion = isset($questionData['id']) && is_numeric($questionData['id']);

                $qPayload = [
                    'question_text' => $questionData['questionText'],
                    'point' => $questionData['points'],
                ];

                if (isset($questionData['questionImage']) && $questionData['questionImage'] instanceof \Illuminate\Http\UploadedFile) {
                    $qPayload['media_path'] = $questionData['questionImage']->store('quiz_media/images', 'public');
                } elseif (isset($questionData['questionImage']) && is_string($questionData['questionImage']) && empty($questionData['questionImage']) && $isExistingQuestion) {
                    // Logic to handle clearing the image if needed (assuming empty string means clear)
                    // You might need to add file unlink logic here if you store files externally
                    $qPayload['media_path'] = null;
                } elseif (isset($questionData['questionImage']) && is_string($questionData['questionImage']) && !empty($questionData['questionImage'])) {
                    // Keep the existing path if a non-empty string URL is passed
                }

                if ($isExistingQuestion) {
                    $question = $quiz->questions()->find($questionData['id']);
                    $question->update($qPayload);
                } else {
                    $question = $quiz->questions()->create($qPayload);
                }

                $existingAIds = collect($questionData['choices'])
                    ->pluck('id')
                    ->filter(fn($val) => is_numeric($val))
                    ->toArray();

                $question->answers()->whereNotIn('id', $existingAIds)->delete();

                foreach ($questionData['choices'] as $choiceData) {
                    $isExistingAnswer = isset($choiceData['id']) && is_numeric($choiceData['id']);

                    $aPayload = [
                        'answer_text' => $choiceData['text'],
                        'is_correct' => $choiceData['correct'] ?? false,
                        'quiz_question_id' => $question->id
                    ];

                    if (isset($choiceData['img']) && $choiceData['img'] instanceof \Illuminate\Http\UploadedFile) {
                        $aPayload['media_path'] = $choiceData['img']->store('quiz_media/answers', 'public');
                    } elseif (isset($choiceData['img']) && is_string($choiceData['img']) && empty($choiceData['img']) && $isExistingAnswer) {
                        // Logic to handle clearing the image if needed
                        $aPayload['media_path'] = null;
                    } elseif (isset($choiceData['img']) && is_string($choiceData['img']) && !empty($choiceData['img'])) {
                        // Keep the existing path if a non-empty string URL is passed
                    }

                    if ($isExistingAnswer) {
                        $question->answers()->where('id', $choiceData['id'])->update($aPayload);
                    } else {
                        $question->answers()->create($aPayload);
                    }
                }
            }

            $quiz->update([
                'total_point' => $totalPoints,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Quiz berhasil diperbarui!',
                'quiz_id' => $quiz->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui kuis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $quiz = Quizzes::with('questions.answers')->findOrFail($id);

            $mediaPaths = [];

            // Kumpulkan semua path media untuk dihapus
            foreach ($quiz->questions as $question) {
                if ($question->media_path) {
                    $mediaPaths[] = $question->media_path;
                }

                foreach ($question->answers as $answer) {
                    if ($answer->media_path) {
                        $mediaPaths[] = $answer->media_path;
                    }
                }
            }

            $quiz->delete();

            // Hapus file-file dari storage
            if (!empty($mediaPaths)) {
                Storage::disk('public')->delete($mediaPaths);
            }

            DB::commit();

            return response()->json([
                'message' => 'Quiz berhasil dihapus!',
                'success' => true,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quiz tidak ditemukan.',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus kuis.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
