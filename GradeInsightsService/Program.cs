using Microsoft.Extensions.ML;
using GradeInsightsService.MLModels;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

// Enable CORS so the PHP application or direct client calls are accepted
builder.Services.AddCors(options =>
{
    options.AddDefaultPolicy(policy =>
    {
        policy.AllowAnyOrigin()
              .AllowAnyHeader()
              .AllowAnyMethod();
    });
});

// Configure listening URL (Default: http://localhost:5000)
builder.WebHost.UseUrls("http://localhost:5000");

// Path to trained ML.NET model
var modelFilePath = Path.Combine(builder.Environment.ContentRootPath, "MLModels", "grade_model.zip");

// Ensure model exists before initializing PredictionEnginePool to avoid file missing exceptions
ModelTrainer.EnsureModelExists(modelFilePath);

// Register the trained ML.NET model into ASP.NET Core DI
builder.Services.AddPredictionEnginePool<StudentGradeInput, StudentGradePrediction>()
    .FromFile(modelName: "GradePredictionModel", filePath: modelFilePath, watchForChanges: true);

var app = builder.Build();

app.UseCors();

if (app.Environment.IsDevelopment() || true)
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.MapControllers();

Console.WriteLine("=================================================");
Console.WriteLine(" Maplewood Grade Insights Service (ML.NET)");
Console.WriteLine(" Listening at: http://localhost:5000");
Console.WriteLine(" Swagger UI:   http://localhost:5000/swagger");
Console.WriteLine(" Predict API:  http://localhost:5000/api/insights/predict");
Console.WriteLine("=================================================");

app.Run();
