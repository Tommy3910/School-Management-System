using Microsoft.ML;
using Microsoft.ML.Data;

namespace GradeInsightsService.MLModels
{
    public static class ModelTrainer
    {
        public static void EnsureModelExists(string modelPath)
        {
            var dir = Path.GetDirectoryName(modelPath);
            if (!string.IsNullOrEmpty(dir) && !Directory.Exists(dir))
            {
                Directory.CreateDirectory(dir);
            }

            if (File.Exists(modelPath))
            {
                return;
            }

            Console.WriteLine($"[ML.NET] Model file not found at '{modelPath}'. Training initial grade model...");

            var mlContext = new MLContext(seed: 42);

            // Sample synthetic training dataset representing academic correlations
            var trainingData = new List<StudentGradeInput>
            {
                new() { StudyHours = 2f,  AttendancePercentage = 60f, PreviousGrade = 50f, Score = 52f },
                new() { StudyHours = 4f,  AttendancePercentage = 70f, PreviousGrade = 55f, Score = 58f },
                new() { StudyHours = 6f,  AttendancePercentage = 75f, PreviousGrade = 62f, Score = 65f },
                new() { StudyHours = 8f,  AttendancePercentage = 80f, PreviousGrade = 68f, Score = 71f },
                new() { StudyHours = 10f, AttendancePercentage = 85f, PreviousGrade = 72f, Score = 76f },
                new() { StudyHours = 12f, AttendancePercentage = 90f, PreviousGrade = 78f, Score = 82f },
                new() { StudyHours = 14f, AttendancePercentage = 92f, PreviousGrade = 82f, Score = 86f },
                new() { StudyHours = 16f, AttendancePercentage = 95f, PreviousGrade = 85f, Score = 89f },
                new() { StudyHours = 18f, AttendancePercentage = 96f, PreviousGrade = 90f, Score = 93f },
                new() { StudyHours = 20f, AttendancePercentage = 98f, PreviousGrade = 94f, Score = 96f },
                new() { StudyHours = 5f,  AttendancePercentage = 90f, PreviousGrade = 60f, Score = 67f },
                new() { StudyHours = 15f, AttendancePercentage = 70f, PreviousGrade = 75f, Score = 77f },
                new() { StudyHours = 10f, AttendancePercentage = 95f, PreviousGrade = 88f, Score = 90f },
                new() { StudyHours = 1f,  AttendancePercentage = 50f, PreviousGrade = 45f, Score = 42f },
                new() { StudyHours = 22f, AttendancePercentage = 99f, PreviousGrade = 96f, Score = 98f },
            };

            var dataView = mlContext.Data.LoadFromEnumerable(trainingData);

            // Build pipeline: Concatenate features -> Train with SDCA or FastTree Regressor
            var pipeline = mlContext.Transforms.Concatenate("Features",
                    nameof(StudentGradeInput.StudyHours),
                    nameof(StudentGradeInput.AttendancePercentage),
                    nameof(StudentGradeInput.PreviousGrade))
                .Append(mlContext.Regression.Trainers.Sdca(labelColumnName: "Score", maximumNumberOfIterations: 200));

            var model = pipeline.Fit(dataView);

            mlContext.Model.Save(model, dataView.Schema, modelPath);
            Console.WriteLine($"[ML.NET] Initial model trained and saved to '{modelPath}'.");
        }
    }
}
