import logo from '../assets/images/logo.png'

export default function WelcomePage() {
  return (
    <div className="bg-white min-h-screen font-poppins flex flex-col items-center justify-center px-6 text-center">
      <img
        src={logo}
        alt="JOI Foods"
        className="w-[85px] h-[85px] rounded-full mb-6"
      />
      <h1 className="text-[24px] font-bold text-dark leading-normal mb-2">
        Welcome to JOI Foods
      </h1>
      <p className="text-[14px] text-body leading-6 max-w-[300px] mb-8">
        Scan the QR code at your table or counter to start ordering delicious food.
      </p>
    </div>
  )
}
